<?php

use CodeIgniter\I18n\Time;

if (!function_exists('render_menu')) {
    function render_menu(int $rol_id): string
    {
        $db = \Config\Database::connect();

        $sql = "SELECT m.* FROM modulos m
                JOIN roles_modulos rm ON rm.modulo_id = m.id AND rm.rol_id = ?
                WHERE m.activo = 1
                ORDER BY m.orden";

        $modules = $db->query($sql, [$rol_id])->getResult();

        $parents = [];
        $children = [];
        foreach ($modules as $m) {
            if ($m->padre_id === null) {
                $parents[] = $m;
            } else {
                $children[$m->padre_id][] = $m;
            }
        }

        $sectionMap = [
            10  => 'Principal',
            20  => 'Gestión',
            130 => 'Reportes',
            140 => 'Configuración',
        ];

        $html = '';
        $currentSection = '';

        helper('url');

        foreach ($parents as $parent) {
            $section = '';
            foreach ($sectionMap as $orden => $name) {
                if ($parent->orden >= $orden) {
                    $section = $name;
                }
            }

            if ($section !== '' && $section !== $currentSection) {
                $currentSection = $section;
                $html .= '<li class="side-nav-title">' . esc($section) . '</li>';
            }

            $hasChildren = !empty($children[$parent->id]);
            $collapseId = 'sidebar' . ucfirst($parent->codigo);

            $html .= '<li class="side-nav-item">';

            if ($hasChildren) {
                $html .= '<a data-bs-toggle="collapse" href="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '" class="side-nav-link">';
                $html .= '<span class="menu-icon"><i data-lucide="' . esc($parent->icono, 'attr') . '"></i></span>';
                $html .= '<span class="menu-text">' . esc($parent->nombre) . '</span>';
                $html .= '<span class="menu-arrow"></span>';
                $html .= '</a>';
                $html .= '<div class="collapse" id="' . $collapseId . '">';
                $html .= '<ul class="sub-menu">';

                foreach ($children[$parent->id] as $child) {
                    $html .= '<li class="side-nav-item">';
                    $html .= '<a href="' . base_url(trim($child->ruta, '/')) . '" class="side-nav-link">';
                    $html .= '<span class="menu-text">' . esc($child->nombre) . '</span>';
                    $html .= '</a>';
                    $html .= '</li>';
                }

                $html .= '</ul></div>';
            } else {
                $html .= '<a href="' . base_url(trim($parent->ruta, '/')) . '" class="side-nav-link">';
                $html .= '<span class="menu-icon"><i data-lucide="' . esc($parent->icono, 'attr') . '"></i></span>';
                $html .= '<span class="menu-text">' . esc($parent->nombre) . '</span>';
                $html .= '</a>';
            }

            $html .= '</li>';
        }

        return $html;
    }
}
