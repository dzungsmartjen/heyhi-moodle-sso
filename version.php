<?php
// Example plugin: SSO from Moodle to HeyHi. Install into <moodle>/local/heyhi.

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_heyhi';
$plugin->version   = 2026111000;
$plugin->requires  = 2024042200; // Moodle 4.4 (needs the primary_extend navigation hook).
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.1.0';
