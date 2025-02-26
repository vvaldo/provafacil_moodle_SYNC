<?php

// This file is part of the eMailTest plugin for Moodle - http://moodle.org/
//
// eMailTest is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// eMailTest is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information for eMailTest (also called MailTest).
 *
 * @package    local_provafacil_sync
 * @copyright  2015-2024 UnISCED. - www.unisced.edu.mz
 * @author     osvaldo Simone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_provafacil_sync'; // Nome do componente.
$plugin->version = 2024111800; // Data no formato YYYYMMDDXX (XX = número da versão do dia).
$plugin->requires = 2022041900; // Versão mínima do Moodle (3.11 ou superior, ajuste conforme necessário).
$plugin->maturity = MATURITY_STABLE; // Maturidade do plugin: ALPHA, BETA ou STABLE.
$plugin->release = '1.0'; // Número da versão do plugin.