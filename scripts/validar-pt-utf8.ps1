param(
    [switch]$WarnOnly
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot '..')

$sourceGlobs = @(
    'resources/views/**/*.blade.php'
    'app/Http/Requests/**/*.php'
    'app/Http/Controllers/**/*.php'
    'app/Services/**/*.php'
    'database/seeders/**/*.php'
    'routes/**/*.php'
)

$excludeDirs = @(
    'storage/framework/views'
    'bootstrap/cache'
    'public/build'
    'node_modules'
    'vendor'
)

$utf8Strict = New-Object System.Text.UTF8Encoding($false, $true)
$cp1252 = [System.Text.Encoding]::GetEncoding(1252)

$nonUtf8 = New-Object System.Collections.Generic.List[object]
$utf8Bom = New-Object System.Collections.Generic.List[string]
$mojibakeHits = New-Object System.Collections.Generic.List[object]
$accentWarnings = New-Object System.Collections.Generic.List[object]

$termRegexes = @(
    '\bUsuarios\b',
    '\bGestao\b',
    '\bConfiguracoes\b',
    '\bRelatorios\b',
    '\bNotificacoes\b',
    '\bAcoes\b',
    '\bEdicao\b',
    '\bobrigatorio\b',
    '\binvalido\b',
    'Papeis e Acessos',
    'Salvar configuracoes',
    'Notificar leilao',
    'configuracao de provedor'
)

function Is-ExcludedPath {
    param([string]$fullPath)

    $normalized = $fullPath.Replace('\', '/')
    foreach ($dir in $excludeDirs) {
        $dirNorm = ($dir.Replace('\', '/') + '/')
        if ($normalized -like "*/$dirNorm*") {
            return $true
        }
    }
    return $false
}

function Resolve-SourceFiles {
    $files = New-Object System.Collections.Generic.List[string]

    foreach ($glob in $sourceGlobs) {
        $resolved = Join-Path $repoRoot $glob
        $items = Get-ChildItem -Path $resolved -File -ErrorAction SilentlyContinue
        foreach ($item in $items) {
            if (-not (Is-ExcludedPath -fullPath $item.FullName)) {
                $files.Add($item.FullName)
            }
        }
    }

    return $files | Sort-Object -Unique
}

$files = Resolve-SourceFiles

foreach ($file in $files) {
    [byte[]]$bytes = [System.IO.File]::ReadAllBytes($file)
    $hasBom = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF

    if ($hasBom) {
        $utf8Bom.Add($file)
    }

    try {
        $text = $utf8Strict.GetString($bytes)
    } catch {
        $preview = $cp1252.GetString($bytes)
        $nonUtf8.Add([pscustomobject]@{
            File = $file
            Preview = ($preview -split "`r?`n" | Select-Object -First 1)
        })
        continue
    }

    $lines = $text -split "`r?`n"
    for ($i = 0; $i -lt $lines.Length; $i++) {
        $line = $lines[$i]

        $hasMojibake = $line.Contains([char]0x00C3) -or $line.Contains([char]0x00C2) -or $line.Contains([char]0xFFFD)
        if ($hasMojibake) {
            $mojibakeHits.Add([pscustomobject]@{
                File = $file
                Line = $i + 1
                Content = $line.Trim()
            })
        }

        if ($line -notmatch '[''"<>]') {
            continue
        }

        if ($line -match "->name\('|route\('|slug'|_id'|_json'|configuracoes_|notificacoes_|usuarios_|papeis_|@include\(|'route'\s*=>|str_starts_with\(|x-show=""aba ===|@click=""aba =|^\s*\$|^\s*\{\{\s*\$") {
            continue
        }

        foreach ($regex in $termRegexes) {
            if ($line -match $regex) {
                $accentWarnings.Add([pscustomobject]@{
                    File = $file
                    Line = $i + 1
                    Match = $regex
                    Content = $line.Trim()
                })
                break
            }
        }
    }
}

Write-Host ""
Write-Host "=== Auditoria UTF-8 / PT-BR ==="
Write-Host ("Arquivos analisados: {0}" -f $files.Count)
Write-Host ("Nao UTF-8: {0}" -f $nonUtf8.Count)
Write-Host ("UTF-8 com BOM: {0}" -f $utf8Bom.Count)
Write-Host ("Mojibake: {0}" -f $mojibakeHits.Count)
Write-Host ("Possiveis termos sem acento: {0}" -f $accentWarnings.Count)

if ($nonUtf8.Count -gt 0) {
    Write-Host ""
    Write-Host "[Nao UTF-8]"
    $nonUtf8 | Select-Object -First 50 | ForEach-Object {
        Write-Host ("- {0}" -f $_.File)
    }
}

if ($utf8Bom.Count -gt 0) {
    Write-Host ""
    Write-Host "[UTF-8 com BOM]"
    $utf8Bom | Select-Object -First 50 | ForEach-Object {
        Write-Host ("- {0}" -f $_)
    }
}

if ($mojibakeHits.Count -gt 0) {
    Write-Host ""
    Write-Host "[Mojibake]"
    $mojibakeHits | Select-Object -First 100 | ForEach-Object {
        Write-Host ("- {0}:{1} -> {2}" -f $_.File, $_.Line, $_.Content)
    }
}

if ($accentWarnings.Count -gt 0) {
    Write-Host ""
    Write-Host "[Possiveis termos sem acento]"
    $accentWarnings | Select-Object -First 150 | ForEach-Object {
        Write-Host ("- {0}:{1} -> {2}" -f $_.File, $_.Line, $_.Content)
    }
}

$hasIssues = $nonUtf8.Count -gt 0 -or $utf8Bom.Count -gt 0 -or $mojibakeHits.Count -gt 0 -or $accentWarnings.Count -gt 0

if ($hasIssues -and -not $WarnOnly) {
    exit 1
}

exit 0
