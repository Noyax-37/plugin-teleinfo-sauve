<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

//$versionIdentique = ' La version stable est identique, vous pouvez basculer dessus sns aucun problème.';
//global $versionIdentique;

function teleinfo_install() {
    teleinfo_update(false);
}

function teleinfo_update($direct=true) {
    $versionIdentique = '';
    $core_version = 'Inconnue';
    $packagesjson = dirname(__FILE__) . '/packages.json'; 
    if (file_exists($packagesjson)){
        log::add('teleinfo','warning','Suppression du fichier packages.json'); 
        unlink($packagesjson); 
    } 
    $ressources = dirname(__FILE__) . '../ressources/'; 
    if (file_exists($ressources)){ 
        log::add('teleinfo','warning',"suppression du répertoire 'ressources'");
        rmdir($ressources); 
    } 
    $postinstall = dirname(__FILE__) . '../resources/post-install.sh'; 
    if (file_exists($postinstall)){ 
        log::add('teleinfo','warning','suppression du fichier post-install.sh');
        unlink($postinstall); 
    } 
    if (!file_exists(dirname(__FILE__) . '/info.json')) {
        log::add('teleinfo','warning','Pas de fichier info.json');
        goto step2;
    }
    $data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
    if (!is_array($data)) {
        log::add('teleinfo','warning','Impossible de décoder le fichier info.json (non bloquant ici)');
        goto step2;
    }
    try {
        $core_version = $data['pluginVersion'];
        config::save('version', $core_version, 'teleinfo');
    } catch (\Exception $e) {
        log::add('teleinfo','warning','Pas de version de plugin (non bloquant ici)');
        goto step2;
    }
    try {
        $changelog = $data['changelog'];
    } catch (\Exception $e) {
        log::add('teleinfo','warning','Pas de changelog (non bloquant ici)');
        goto step2;
    }

    try {
        $changelog_beta = $data['changelog_beta'];
    } catch (\Exception $e) {
        log::add('teleinfo','warning','Pas de changelog béta (non bloquant ici)');
        goto step2;
    }


    try {
        if (file_get_contents($changelog) == file_get_contents($changelog_beta)){
            $versionIdentique = ' Les versions STABLE et BETA sont identiques, si vous êtes en BETA il vaudrait mieux passer en STABLE cela ne change absolument rien pour vous.';
        } 
    } catch (\Exception $e) {
        log::add('teleinfo','warning','un des fichiers changelog n existe pas (non bloquant ici)');
        goto step2;
    }


    step2:
    if ($direct){
        message::add('teleinfo', 'Mise à jour du plugin Téléinfo en cours...');
        log::add('teleinfo','debug','teleinfo_update');
        log::add('teleinfo','info','*****************************************************');
        log::add('teleinfo','info','*********** Mise à jour du plugin teleinfo **********');
        } else {
        message::add('teleinfo', 'Installation du plugin Téléinfo en cours...');
        log::add('teleinfo','debug','teleinfo_install');
        log::add('teleinfo','info','*****************************************************');
        log::add('teleinfo','info','********** Installation du plugin teleinfo **********');
        }
    log::add('teleinfo','info','*****************************************************');
    log::add('teleinfo','info','**         Core version    : '. $core_version. str_repeat(" ",22-strlen($core_version)) . '**');
    log::add('teleinfo','info','*****************************************************');
    
    if (teleinfo::deamonRunning()) {
        teleinfo::deamon_stop();
    }

    // mise à jour stat si elles n'existent pas
    log::add('teleinfo', 'info', "-------- Commandes des stats si elles n'existent pas ---------");

    $array = array("STAT_TODAY_INDEX00","STAT_TODAY_INDEX00_COUT","STAT_YESTERDAY_INDEX00","STAT_YESTERDAY_INDEX00_COUT",
                    "STAT_TODAY_INDEX01","STAT_TODAY_INDEX01_COUT","STAT_YESTERDAY_INDEX01","STAT_YESTERDAY_INDEX01_COUT",
                    "STAT_TODAY_INDEX02","STAT_TODAY_INDEX02_COUT","STAT_YESTERDAY_INDEX02","STAT_YESTERDAY_INDEX02_COUT",
                    "STAT_TODAY_INDEX03","STAT_TODAY_INDEX03_COUT","STAT_YESTERDAY_INDEX03","STAT_YESTERDAY_INDEX03_COUT",
                    "STAT_TODAY_INDEX04","STAT_TODAY_INDEX04_COUT","STAT_YESTERDAY_INDEX04","STAT_YESTERDAY_INDEX04_COUT",
                    "STAT_TODAY_INDEX05","STAT_TODAY_INDEX05_COUT","STAT_YESTERDAY_INDEX05","STAT_YESTERDAY_INDEX05_COUT",
                    "STAT_TODAY_INDEX06","STAT_TODAY_INDEX06_COUT","STAT_YESTERDAY_INDEX06","STAT_YESTERDAY_INDEX06_COUT",
                    "STAT_TODAY_INDEX07","STAT_TODAY_INDEX07_COUT","STAT_YESTERDAY_INDEX07","STAT_YESTERDAY_INDEX07_COUT",
                    "STAT_TODAY_INDEX08","STAT_TODAY_INDEX08_COUT","STAT_YESTERDAY_INDEX08","STAT_YESTERDAY_INDEX08_COUT",
                    "STAT_TODAY_INDEX09","STAT_TODAY_INDEX09_COUT","STAT_YESTERDAY_INDEX09","STAT_YESTERDAY_INDEX09_COUT",
                    "STAT_TODAY_INDEX10","STAT_TODAY_INDEX10_COUT","STAT_YESTERDAY_INDEX10","STAT_YESTERDAY_INDEX10_COUT");
    
    foreach (eqLogic::byType('teleinfo') as $eqLogic) {
        foreach ($array as $value){
            $cmd = $eqLogic->getCmd('info', $value);
            if (!is_object($cmd)) {
                log::add('teleinfo', 'info', "Nouvelle STAT => compteur '". $eqLogic->getName() . "' " . $value);
                if (strpos($value,'COUT')<>0) {
                    $unite = ('€');
                }else{
                    $unite = ('Wh');
                }
                $cmd = new teleinfoCmd();
                $cmd->setName($value);
                $cmd->setEqLogic_id($eqLogic->getId());
                $cmd->setLogicalId($value);
                $cmd->setType('info');
                $cmd->setUnite($unite);
                $cmd->setConfiguration('info_conso', $value);
                $cmd->setConfiguration('type', 'stat');
                $cmd->setConfiguration('historizeMode', 'none');
                $cmd->setDisplay('generic_type', 'DONT');
                $cmd->setSubType('numeric');
                $cmd->setIsHistorized(1);
                //$cmd->setEventOnly(1);
                $cmd->setIsVisible(0);
                $cmd->save();
                $cmd->refresh();
            }

        }
    }


    //fin mise à jour stat
    
    // installation des crons
    log::add('teleinfo','info','** (ré)installation des crons si nécessaire **');
    $cron = cron::byClassAndFunction('teleinfo', 'calculateOtherStats');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('teleinfo');
        $cron->setFunction('calculateOtherStats');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('10 00 * * *');
        $cron->save();
    }
    $cron->stop();

    $crontoday = cron::byClassAndFunction('teleinfo', 'calculateTodayStats');
    if (!is_object($crontoday)) {
        $crontoday = new cron();
        $crontoday->setClass('teleinfo');
        $crontoday->setFunction('calculateTodayStats');
        $crontoday->setEnable(1);
        $crontoday->setDeamon(0);
        $crontoday->setSchedule('*/5 * * * *');
        $crontoday->save();
    }
    $crontoday->stop();

    $cronclean = cron::byClassAndFunction('teleinfo', 'cleanDBTeleinfo');
    if (!is_object($cronclean)) {
        $cronclean = new cron();
        $cronclean->setClass('teleinfo');
        $cronclean->setFunction('cleanDBTeleinfo');
        $cronclean->setEnable(1);
        $cronclean->setDeamon(0);
        $cronclean->setSchedule('25 0 * * 1');
        $cronclean->save();
    }
    $cronclean->stop();
    
    message::removeAll('teleinfo');
    if ($direct){
        message::add('teleinfo', 'Mise à jour du plugin Téléinfo terminée, vous êtes en version ' . $core_version . '.' . $versionIdentique);
    } else {
        message::add('teleinfo', 'Installation du plugin Téléinfo terminée, vous êtes en version ' . $core_version . '.' . $versionIdentique);

    }
    message::add('teleinfo', "n'oubliez pas de (ré) installer les dépendances");
    teleinfo::cron();
}

function teleinfo_remove() {
    if (teleinfo::deamonRunning()) {
        teleinfo::deamon_stop();
    }
    $cron = cron::byClassAndFunction('teleinfo', 'CalculateOtherStats');
    if (is_object($cron)) {
        $cron->remove();
    }
    $crontoday = cron::byClassAndFunction('teleinfo', 'CalculateTodayStats');
    if (is_object($crontoday)) {
        $crontoday->remove();
    }
    $cronclean = cron::byClassAndFunction('teleinfo', 'cleanDBTeleinfo');
    if (is_object($cronclean)) {
        $cronclean->remove();
    }
    message::removeAll('teleinfo');
    message::add('teleinfo', 'Désinstallation du plugin Téléinfo terminée, vous pouvez de nouveau relever les index à la main ;)');
}
