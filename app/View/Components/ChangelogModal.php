<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ChangelogModal extends Component
{
    public string $module;
    public string $version;
    public string $modalId;

    public function __construct(string $module)
    {
        $this->module      = $module;
        $this->version     = config('versiones.' . $module, '—');
        $this->modalId     = 'changelog-' . strtolower($module);
    }

    public function parsedChangelog(): string
    {
        $slug = strtolower($this->module);
        $path = base_path("docs/changelog/{$slug}.md");

        if (! file_exists($path)) {
            return '<p class="text-muted small mb-0">Historial no disponible.</p>';
        }

        return $this->parseMarkdown(file_get_contents($path));
    }

    private function parseMarkdown(string $content): string
    {
        $lines     = explode("\n", $content);
        $html      = '';
        $inList    = false;
        $inVersion = false;
        $skipHead  = true;

        foreach ($lines as $raw) {
            $line = rtrim($raw);

            // Skip file header lines (title, description, links) until first version
            if ($skipHead) {
                if (str_starts_with($line, '## ')) {
                    $skipHead = false;
                } else {
                    continue;
                }
            }

            if (str_starts_with($line, '## ')) {
                if ($inList)    { $html .= '</ul>'; $inList = false; }
                if ($inVersion) { $html .= '</div>'; }
                $inVersion = true;

                $title = ltrim($line, '# ');
                if (str_contains($title, ' — ')) {
                    [$ver, $date] = array_pad(explode(' — ', $title, 2), 2, '');
                } elseif (str_contains($title, ' – ')) {
                    [$ver, $date] = array_pad(explode(' – ', $title, 2), 2, '');
                } elseif (str_contains($title, ' - ')) {
                    [$ver, $date] = array_pad(explode(' - ', $title, 2), 2, '');
                } else {
                    [$ver, $date] = [$title, ''];
                }
                $ver  = trim($ver);
                $date = trim($date);

                $html .= '<div class="changelog-entry mb-3">';
                $html .= '<h6 class="mb-1">'
                    . '<span class="badge badge-dark mr-1">' . e($ver) . '</span>';
                if ($date) {
                    $html .= '<small class="text-muted">' . e($date) . '</small>';
                }
                $html .= '</h6>';

            } elseif (str_starts_with($line, '### ')) {
                if ($inList) { $html .= '</ul>'; $inList = false; }

                $section = trim(ltrim($line, '# '));
                $styles  = [
                    'Security'      => ['danger',    '🔐'],
                    'Fixed'         => ['warning',   '🔧'],
                    'Added'         => ['success',   '✨'],
                    'Changed'       => ['info',      '🔄'],
                    'Removed'       => ['secondary', '🗑'],
                    'Deprecated'    => ['secondary', '⚠'],
                    'Documentation' => ['primary',   '📄'],
                ];
                [$color, $icon] = $styles[$section] ?? ['dark', '•'];
                $html .= '<p class="mb-1 small font-weight-bold text-' . $color . '">'
                    . $icon . ' ' . e($section) . '</p>';

            } elseif (preg_match('/^\s*- (.+)/', $line, $m)) {
                if (! $inList) { $html .= '<ul class="small pl-3 mb-1">'; $inList = true; }
                $html .= '<li>' . $this->inline($m[1]) . '</li>';

            } elseif (str_starts_with($line, '> ')) {
                if ($inList) { $html .= '</ul>'; $inList = false; }
                $html .= '<p class="small text-muted font-italic mb-1">'
                    . $this->inline(substr($line, 2)) . '</p>';

            } elseif (trim($line) === '---') {
                // version separator — nothing to emit
            }
        }

        if ($inList)    $html .= '</ul>';
        if ($inVersion) $html .= '</div>';

        return $html ?: '<p class="text-muted small mb-0">Sin entradas registradas.</p>';
    }

    private function inline(string $text): string
    {
        $text = e($text);
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/`(.+?)`/',        '<code>$1</code>',    $text);
        $text = preg_replace('/\*(.+?)\*/',      '<em>$1</em>',        $text);
        return $text;
    }

    public function render()
    {
        return view('components.changelog-modal', [
            'parsedChangelog' => $this->parsedChangelog(),
        ]);
    }
}
