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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

sendVarToJS('version', config::byKey('version', 'teleinfo', 'unknown', true));
include_file('desktop', 'teleinfo.config', 'js', 'teleinfo');

$port = config::byKey('port', 'teleinfo');
$activation_Modem = config::byKey('activation_Modem', 'teleinfo');
$activation_Mqtt = config::byKey('activation_Mqtt', 'teleinfo');
$mqtt_broker = config::byKey('mqtt_broker', 'teleinfo');
$mqtt_port = config::byKey('mqtt_port', 'teleinfo');
$mqtt_topic = config::byKey('mqtt_topic', 'teleinfo');
$mqtt_username = config::byKey('mqtt_username', 'teleinfo');
$mqtt_password = config::byKey('mqtt_password', 'teleinfo');
$core_version = '1.1.1';


if (!file_exists(dirname(__FILE__) . '/info.json')) {
    log::add('teleinfo','warning','Pas de fichier info.json');
}
$data = json_decode(file_get_contents(dirname(__FILE__) . '/info.json'), true);
if (!is_array($data)) {
    log::add('teleinfo','warning',__('Impossible de décoder le fichier info.json', __FILE__));
}
try {
    $core_version = $data['pluginVersion'];
} catch (\Exception $e) {
    log::add('teleinfo','warning',__('Impossible de récupérer la version.', __FILE__));
}
?>

<form class="form-horizontal">
    <fieldset>
        <legend><i class="icon fas fa-bolt"></i> {{Compteurs   => ATTENTION: toute modification des configurations ci-dessous nécessite le redémarrage du daemon}}</legend>
        <div class="form-group">
            <div class="col-sm-8">
                <a class="btn btn-warning config_Generale" data-choix="config_Generale"><i class="fas fa-cogs"></i> {{Configuration Générale du plugin}}</a>
        		<a class="btn btn-success choix_Modem" data-choix="choix_Modem"><i class="fas fa-paperclip"></i> {{Configuration de la partie Modem}}</a>
        		<a class="btn btn-success choix_Mqtt" data-choix="choix_Mqtt"><i class="fas fa-paperclip"></i> {{Configuration de la partie MQTT}}</a>
        	</div>
        </div>
        <div class="choixModem form-group div_local">
            <legend class="modem_non"><i class="icon fas fa-warning"></i> {{Il faut auparavant cocher "Utilisation d'un modem téléinformation" dans la partie configuration générale du plugin}}</legend>
            <div class="modem_oui">
                <div class="form-group div_local">
                    <div class="form-group div_local">
                    </div>
                    <legend><i class="icon fas fa-cog"></i> {{Modem : configuration Générale}}</legend>
                    <div class="form-group div_local">
                    </div>
                    <label class="col-lg-4 control-label">{{Cycle sommeil acquisition teleinfo (s)}}<sup><i class="fas fa-question-circle tooltips" title="{{temps entre 2 interrogations de la TIC, limite les données à enregistrer}}" style="font-size : 1em;color:grey;"></i></sup></label>
                    <div class="col-lg-1">
                        <input class="configKey form-control" data-l1key="cycle_sommeil" placeholder="{{0.5}}"/>
                    </div>
                    <label class="col-sm-4 control-label"> </label>
                    <div class="form-group div_local">
                    </div>
                    <label class="col-lg-4 control-label">{{Port du modem 1}}</label>
                    <div class="col-lg-4">
                        <select id="select_port" class="configKey form-control" data-l1key="port">
                            <option value="">Aucun</option>
                            <?php
                            foreach (jeedom::getUsbMapping() as $name => $value) {
                                echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                            }
                            echo '<option value="serie">Modem Série</option>';
                            ?>
                        </select>
                        <input id="port_serie" class="configKey form-control" data-l1key="modem_serie_addr" style="margin-top:5px;display:none" placeholder="{{Renseigner le port série (ex : /dev/ttyS0)}}"/>
                        <script>
                        $( "#select_port" ).change(function() {
                            $( "#select_port option:selected" ).each(function() {
                                if($( this ).val() == "serie"){
                                    $("#port_serie").show();
                                }
                                else{
                                    $("#port_serie").hide();
                                }
                            });
                        });
                        </script>
                    </div>
                    <span class="col-lg-4"><a class="btn btn-sm btn-info" id="btn_detect_type"><i class="fas fa-magic"></i> {{Détection Automatique}}</a></span>
                </div>

                <div class="form-group div_local" style="display:none">
                    <label class="col-lg-4 control-label">Activer le compteur 2 <sup><i class="fas fa-question-circle tooltips" title="{{En cas d'utilisation d'un second modem téléinfo}}" style="font-size : 1em;color:grey;"></i></sup></label>
                    <div id="div_activation_production" class="col-lg-4 tooltips" title="{{En cas d'utilisation d'un second modem téléinfo}}">
                        <input type="checkbox" id="activation_production" class="configKey" data-l1key="activation_production" placeholder="{{Activer}}"/>
                        <label for="activation_production"></label>
                    </div>
                </div>
                <div class="form-group div_local">
                    <label class="col-lg-4 control-label">{{Port du modem 2}}</label>
                    <div class="col-lg-4">
                        <select id="select_port_modem2" class="configKey form-control" data-l1key="port_modem2">
                            <option value="">Aucun</option>
                            <?php
                            foreach (jeedom::getUsbMapping() as $name => $value) {
                                echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                            }
                            echo '<option value="serie">Modem Série</option>';
                            ?>
                        </select>
                        <input id="port_serie_modem2" class="configKey form-control" data-l1key="modem_serie_compteur2_addr" style="margin-top:5px;display:none" placeholder="{{Renseigner le port série (ex : /dev/ttyS0)}}"/>
                        <script>
                        $( "#select_port_modem2" ).change(function() {
                            $( "#select_port_modem2 option:selected" ).each(function() {
                                if($( this ).val() == "serie"){
                                    $("#port_serie_modem2").show();
                                }
                                else{
                                    $("#port_serie_modem2").hide();
                                }
                            });
                        });
                        </script>
                    </div>
                    <span class="col-lg-4"><a class="btn btn-sm btn-info" id="btn_detect_type_modem2"><i class="fas fa-magic"></i> {{Détection Automatique}}</a></span>
                </div>

                <div class="form-group div_local">
                    <label class="col-lg-4 control-label">Modem 2 compteurs (Cartelectronic)<sup><i class="fas fa-question-circle tooltips" title="{{Si vous utilisez le modem Cartelectronic en mode 2 compteurs}}" style="font-size : 1em;color:grey;"></i></sup></label>
                    <div id="div_mode_2_cpt" class="col-lg-4 tooltips" title="{{Seulement en cas d'utilisation de 2 compteurs simultanés (Cartelectronic)}}">
                        <input type="checkbox" id="mode_2_cpt" class="configKey" data-l1key="2cpt_cartelectronic" placeholder="{{Actif}}"/>
                        <label for="mode_2_cpt">  </label>
                        <!--<label class="checkbox-inline"><input id="mode_2_cpt" type="checkbox" class="configKey" data-l1key="2cpt_cartelectronic" />{{Actif}}</label>-->
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <div class="choixModem">
            <div class="modem_oui">
                <legend><i class="icon fas fa-cog"></i> {{Modem : configuration avancée}} <i class="fas fa-plus-circle" data-toggle="collapse" href="#OptionsCollapse" role="button" aria-expanded="false" aria-controls="OptionsCollapse"></i></legend>
                <div class="collapse" id="OptionsCollapse">
                    <div class="form-group">
                        <label class="col-lg-4 control-label">{{Compteur 1 communiquant en mode STANDARD}}<sup><i class="fas fa-question-circle tooltips" title="{{Veuillez regarder la documentation pour identifier votre compteur}}" style="font-size : 1em;color:grey;"></i></sup></label>
                        <div id="div_linky" class="col-lg-4 tooltips" title="{{ Veuillez regarder la documentation pour identifier votre compteur}} ">
                            <input type="checkbox" id="linky" class="configKey" data-l1key="linky" placeholder="{{}}"/>
                            <label for="linky">  </label>
                            <label id="label_linky" style="color:red;margin-left:100px;margin-top:-15px;display:none">{{Attention, assurez vous que votre compteur soit en mode standard. Aucune idée ? Se reporter à la documentation.}}</label>
                            <script>
                            $( "#linky" ).change(function() {
                                    if($( this ).value() == "1"){
                                        $("#label_linky").show();
                                    }
                                    else{
                                        $("#label_linky").hide();
                                    }
                            });
                            </script>
                        </div>
                    </div>
                    <div class="form-group div_local">
                        <label class="col-lg-4 control-label">{{Compteur 1 vitesse}}</label>
                        <div class="col-lg-4">
                            <select class="configKey form-control" id="modem_vitesse" data-l1key="modem_vitesse">
                                <option value="">{{Par défaut}}</option>
                                <option style="font-weight: bold;" value="1200">1200</option>
                                <option value="2400">2400</option>
                                <option value="4800">4800</option>
                                <option style="font-weight: bold;" value="9600">9600</option>
                                <option value="19200">19200</option>
                                <option value="38400">38400</option>
                                <option value="56000">56000</option>
                                <option value="115200">115200</option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-lg-4 control-label">{{Compteur 2 communiquant en mode STANDARD}}<sup><i class="fas fa-question-circle tooltips" title="{{Veuillez regarder la documentation pour identifier votre compteur}}" style="font-size : 1em;color:grey;"></i></sup></label>
                        <div id="div_linky_prod" class="col-lg-4 tooltips" title="{{ Veuillez regarder la documentation pour identifier votre compteur }}">
                            <input type="checkbox" id="linky_prod" class="configKey" data-l1key="linky_prod" placeholder="{{}}"/>
                            <label for="linky_prod">  </label>
                            <label id="label_linky_prod" style="color:red;margin-left:100px;margin-top:-15px;display:none">{{Attention, assurez vous que votre compteur soit en mode standard. Aucune idée ? Se reporter à la documentation.}}</label>
                            <script>
                            $( "#linky_prod" ).change(function() {
                                    if($( this ).value() == "1"){
                                        $("#label_linky_prod").show();
                                    }
                                    else{
                                        $("#label_linky_prod").hide();
                                    }
                            });
                            </script>
                        </div>
                    </div>

                    <div class="form-group div_local">
                        <label class="col-lg-4 control-label">{{Compteur 2 vitesse}}</label>
                        <div class="col-lg-4">
                            <select class="configKey form-control" id="modem_compteur2_vitesse" data-l1key="modem_compteur2_vitesse">
                                <option value="">{{Par défaut}}</option>
                                <option style="font-weight: bold;" value="1200">1200</option>
                                <option value="2400">2400</option>
                                <option value="4800">4800</option>
                                <option style="font-weight: bold;" value="9600">9600</option>
                                <option value="19200">19200</option>
                                <option value="38400">38400</option>
                                <option value="56000">56000</option>
                                <option value="115200">115200</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group div_local" style="display:none">
                        <label class="col-lg-4 control-label">Mode 2 compteurs <sup><i class="fas fa-question-circle tooltips" title="{{Si vous utilisez le modem Cartelectronic en mode 2 compteurs}}" style="font-size : 1em;color:grey;"></i></sup></label>
                        <div id="div_mode_2_cpt_production" class="col-lg-4 tooltips" title="{{Seulement en cas d'utilisation de 2 compteurs simultanés (Cartelectronic)}}">
                            <input type="checkbox" id="mode_2_cpt_production" class="configKey" data-l1key="2cpt_cartelectronic_production" placeholder="{{Actif}}"/>
                            <label for="mode_2_cpt_production"> Actif </label>
                            <!--<label class="checkbox-inline"><input id="mode_2_cpt" type="checkbox" class="configKey" data-l1key="2cpt_cartelectronic" />{{Actif}}</label>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="choixMqtt">

            <legend class="mqtt_non"><i class="icon fas fa-warning"></i> {{Il faut auparavant cocher "Activer le MQTT" dans la partie configuration générale du plugin}}</legend>
            <div class="mqtt_oui">
                <legend><i class="icon fas fa-warning"></i> {{Options MQTT}}</legend>
                <div class="form-group">
                    <label class="col-lg-4 control-label">{{Adresse IP du Broker MQTT}}</label>
                    <div class="col-lg-2">
                        <input class="configKey form-control" data-l1key="mqtt_broker" placeholder="{{127.0.0.1}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-4 control-label">{{Port du Broker}}</label>
                    <div class="col-lg-2">
                        <input class="configKey form-control" data-l1key="mqtt_port" placeholder="{{1883}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">{{Nom d'utilisateur (si aucun laisser vide) :}} </label>
                    <div class="col-sm-2">
                        <input class="configKey form-control" data-l1key="mqtt_username" placeholder="{{}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">{{Mot de passe (si aucun laisser vide) :}} </label>
                    <div class="col-sm-2">
                        <input type="password" class="configKey form-control" data-l1key="mqtt_password" placeholder="{{}}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">{{Topic utilisé (si inconnu laisser vide) :}} </label>
                    <div class="col-sm-2">
                        <input class="configKey form-control" data-l1key="mqtt_topic" placeholder=" "/>
                    </div>
                </div>
                <br />
                <div class="a_voir form-group">
                    <label class="col-sm-4 control-label"> </label>
                    <div class="col-sm-2">
                        <a class="btn btn-warning recherche_mqtt" data-choix="recherche_mqtt"><i class="fas fa-cogs"></i> {{Lancer recherche compteurs pendant 5 minutes}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="configGenerale">
            <legend><i class="icon fas fa-warning"></i> {{Options du Plugin}}</legend>
            
            <div class="form-group">
                    <label class="col-lg-4 control-label">{{Bloquer la création automatique des compteurs}} <sup><i class="fas fa-question-circle tooltips" title="{{Interdire la création automatique des nouveaux compteurs}}" style="font-size : 1em;color:grey;"></i></sup></label>
                    <div id="div_auth_new_object" class="col-lg-4 tooltips"  title="{{Interdire la création automatique des nouveaux compteurs}}">
                        <!--<label class="checkbox-inline"><input id="force" type="checkbox" class="configKey" data-l1key="force" />{{Oui}}</label>-->
                        <input type="checkbox" id="auth_new_object" class="configKey" data-l1key="createNewADCO" placeholder="{{Actif}}"/>
                        <label for="auth_new_object">  </label>
                    </div>
                </div>
            <div id="div_activation_Modem" class="form-group">
                <label class="col-lg-4 control-label">{{Utilisation d'un modem téléinformation :}} </label>
                <div class="col-lg-4">
                    <input type="checkbox" id="activation_Modem" class="configKey" data-l1key="activation_Modem" placeholder="{{Activer}}" checked/>
                    <label class="check_modem" for="activation_Modem">{{compléter la partie configuration du modem}}</label>
                    </div>
            </div>
            <div class="form-group div_local">
                <label class="col-lg-4 control-label">{{Activer le MQTT}} <sup><i class="fas fa-question-circle tooltips" title="{{Utilisation d'une TIC en MQTT}}" style="font-size : 1em;color:grey;"></i></sup></label>
                <div id="div_activation_Mqtt" class="col-lg-4 tooltips">
                    <input type="checkbox" id="activation_Mqtt" class="configKey" data-l1key="activation_Mqtt" placeholder="{{Activer}}"/>
                    <label class="check_mqtt" for="activation_Mqtt">{{compléter la partie configuration du MQTT}}</label>
                </div>
            </div>
            <div class="form-group">
        	    <label class="col-lg-4 control-label">{{Adresse IP socket interne (modification dangereuse)}}</label>
        	    <div class="col-lg-2">
        	        <input class="configKey form-control" data-l1key="sockethost" placeholder="{{127.0.0.1}}" />
        	    </div>
            </div>
            <div class="form-group">
                <label class="col-lg-4 control-label">{{Port socket interne (modification dangereuse)}}</label>
                <div class="col-lg-2">
                    <input class="configKey form-control" data-l1key="socketport" placeholder="{{55062}}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">{{Cycle (s)}}</label>
                <div class="col-sm-2">
                    <input class="configKey form-control" data-l1key="cycle" placeholder="0.3"/>
                </div>
            </div>


            <div class="form-group">
                <label class="col-sm-4 control-label">{{Temp. extérieure}} :</label>
                <div class="input-group col-sm-2">
                    <input id="outsideTemp" class="eqLogicAttr form-control input-sm configKey" data-l1key="outside_temp">
                    <span class="input-group-btn">
                        <a class="btn btn-default btn-sm cursor" id="bt_selectoutsideTemp" title="{{Choisir une commande}}"><i class="fas fa-list-alt"></i></a>
                    </span>
                    <span class="input-group-btn">
                        <a class="btn btn-default btn-sm cursor" id="bt_supprTemp" title="{{supprimer}}">suppr</a>
                    </span>
                </div>

            </div>




        </div>
    </fieldset>
    <br /><br /><br /><br /><br /><br />
    <fieldset>
    <legend><i class="icon loisir-pacman1"></i> {{Version}}</legend>
        <div class="form-group">
            <label class="col-lg-4 control-label">Core Teleinfo <sup><i class="fas fa-question-circle tooltips" title="{{C'est la version du programme de connexion au modem}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <span style="top:6px;" class="col-lg-4"><?php echo $core_version; ?></span>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Diagnostique}} <sup><i class="fas fa-question-circle tooltips" title="{{Rechercher la cause d'un disfonctionnement}}" style="font-size : 1em;color:grey;"></i></sup></label>
            <span style="top:6px;" class="col-lg-4"><a class="btn btn-sm btn-info" id="btn_diagnostic" style="position:relative;top:-5px;"><i class="divers-svg"></i> {{Démarrer}}</a></span>
        </div>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-8">
<!--        		<a class="btn btn-warning changeLogLive" data-log="logdebug"><i class="fas fa-cogs"></i> {{Mode debug forcé temporaire des démons}}</a>
        		<a class="btn btn-success changeLogLive" data-log="lognormal"><i class="fas fa-paperclip"></i> {{Remettre niveau de log normal des démons}}</a>
-->        	</div>
        </div>
    </fieldset>
</form>

<script>
        $('.choixModem').hide();
        $('.choixMqtt').hide();
        $('.configGenerale').hide();
        $('.a_voir').hide();


        $('#btn_diagnostic').on('click',function(){
            $('#md_modal').dialog({title: "{{Diagnostique de résolution d'incident}}"});
            $('#md_modal').load('index.php?v=d&plugin=teleinfo&modal=diagnostic').dialog('open');
        });

        $('#bt_savePluginLogConfig').on('click',function(){ // bouton sauvegarde des modifs mode de log
            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
                data: {
                    action: "changeLogLive",
                    level: $('#div_plugin_log').getValues('.configKey')[0],
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $.fn.showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $.fn.showAlert({message: '{{Changement réussi}}', level: 'success'});
                }
            });
        });

		$('#btn_detect_type').on('click',function(){
			if($( "#select_port option:selected" ).val() == "serie"){
				$selectPort = $("#port_serie").val();
                $type = "serie";
			}
			else {
				$selectPort = $( "#select_port option:selected" ).val();
                $type = "usb";
			}

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
                data: {
                    action: "findModemType",
					port: $selectPort,
                    type: $type,
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $.fn.showAlert({message: data.result.message, level: 'danger'});
                        return;
                    }
                    if (data.result.state == 'ok') {
                        console.log(data);
                        $.fn.showAlert({message: data.result.message + " {{N'oubliez pas de sauvegarder.}}", level: 'success'});
                        $("#modem_vitesse").val(data.result.vitesse);
                        $("#linky").prop('checked', data.result.linky);
                    }
                    else {
                        $.fn.showAlert({message: data.result.message, level: 'warning'});
                    }
				}
            });
        });

        $('#btn_detect_type_modem2').on('click',function(){
			if($( "#select_port_modem2 option:selected" ).val() == "serie"){
				$selectPort = $("#port_serie_modem2").val();
                $type = "serie";
			}
			else {
				$selectPort = $( "#select_port_modem2 option:selected" ).val();
                $type = "usb";
			}

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
                data: {
                    action: "findModemType",
					port: $selectPort,
                    type: $type,
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { // si l'appel a bien fonctionné
                    console.log(data);
                    if (data.state != 'ok') {
                        $.fn.showAlert({message: data.result.message, level: 'danger'});
                        return;
                    }
                    if (data.result.state == 'ok') {
                        $.fn.showAlert({message: data.result.message + " {{N'oubliez pas de sauvegarder.}}", level: 'success'});
                        $("#modem_compteur2_vitesse").val(data.result.vitesse);
                        $("#linky_prod").prop('checked', data.result.linky);
                    }
                    else {
                        $.fn.showAlert({message: data.result.message, level: 'warning'});
                    }
				}
            });
        });

        $('.choix_Mqtt').on('click', function () {
            $('.choixModem').hide();
            $('.choixMqtt').show();
            if ($('#activation_Mqtt').prop('checked') == 1) {
                $('.mqtt_oui').show();
                $('.mqtt_non').hide();
            }else{
                $('.mqtt_oui').hide();
                $('.mqtt_non').show();
            }
            $('.configGenerale').hide();
        });

        $('.choix_Modem').on('click', function () {
            $('.choixModem').show();
            $('.choixMqtt').hide();
            $('.configGenerale').hide();
            if ($('#activation_Modem').prop('checked') == 1) {
                $('.modem_oui').show();
                $('.modem_non').hide();
            }else{
                $('.modem_oui').hide();
                $('.modem_non').show();
            }
        });

        $('.config_Generale').on('click', function () {
            $('.choixModem').hide();
            $('.choixMqtt').hide();
            $('.configGenerale').show();
        });

        $('.recherche_mqtt').on('click', function () {
            if ("[recherche_mqtt] : " + $('.configKey[data-l1key=mqtt_topic]').value() == ''){
                topic = "#";
            }else{
                topic = $('.configKey[data-l1key=mqtt_topic]').value();
            }
            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/teleinfo/core/ajax/teleinfo.ajax.php", // url du fichier php
                data: {
                    action: "startMqtt",
    				socketport : ($('.configKey[data-l1key=socketport]').value()),
    				sockethost : $('.configKey[data-l1key=sockethost]').value(),
    				modem : $('.configKey[data-l1key=modem]').value(),
    				mqtt : $('.configKey[data-l1key=mqtt]').value(),
    				mqtt_broker : $('.configKey[data-l1key=mqtt_broker]').value(),
    				mqtt_port : $('.configKey[data-l1key=mqtt_port]').value(),
    				mqtt_topic : topic,
    				mqtt_username : $('.configKey[data-l1key=mqtt_username]').value(),
    				mqtt_password : $('.configKey[data-l1key=mqtt_password]').value(),
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $.fn.showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $.fn.showAlert({message: '{{Réussie}}', level: 'success'});
                }
            });
            
        });

</script>
<?php

include_file('desktop', 'options', 'js', 'teleinfo');
?>