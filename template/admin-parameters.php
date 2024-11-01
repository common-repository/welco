<?php

function welco_admin_parameters_page_html() {
    session_start();

    if(isset($_POST["valider"])){
        if(isset($_POST["boutiquename"])){
            update_option("boutiquename_welco", $_POST["boutiquename"]);
        }
        if(isset($_POST["adresse"])){
            update_option("adresse_welco", $_POST["adresse"]);
        }
        if(isset($_POST["cp"])){
            update_option("cp_welco", $_POST["cp"]);
        }
        if(isset($_POST["ville"])){
            update_option("ville_welco", $_POST["ville"]);
        }
        if(isset($_POST["mail"])){
            update_option("mail_welco", $_POST["mail"]);
        }
        if(isset($_POST["tel"])){
            update_option("tel_welco", $_POST["tel"]);
        }
        if(isset($_POST["mapboxkey"])){
            update_option("mapboxkey_welco", $_POST["mapboxkey"]);
        }
        if(isset($_POST["welcokey"])){
            update_option("welcokey_welco", $_POST["welcokey"]);
        }
        if(isset($_POST["welcoapiurl"])){
            update_option("welcoapiurl_welco", $_POST["welcoapiurl"]);
        }
        if(isset($_POST["welcowidgeturl"])){
            update_option("welcowidgeturl_welco", $_POST["welcowidgeturl"]);
        }
        if(isset($_POST["commandeEnAttenteWelco"])){
            update_option("commandeEnAttenteWelco_welco", $_POST["commandeEnAttenteWelco"]);
        }
        if(isset($_POST["commandeStatutPretPourExpedition"])){
            update_option("commandeStatusPretPourExpedition_welco", $_POST["commandeStatutPretPourExpedition"]);
        }
        if(isset($_POST["commandeStatutExpediee"])){
            update_option("commandeStatutExpediee_welco", $_POST["commandeStatutExpediee"]);
        }
        if(isset($_POST["commandeStatutLivree"])){
            update_option("commandeStatutLivree_welco", $_POST["commandeStatutLivree"]);
        }
        if(isset($_POST["majAutoCron"])){
            update_option("majAutoCron", $_POST["majAutoCron"]);
        }else{
            update_option("majAutoCron", "no");
        }

        $_SESSION["flash_welco"] = "Les informations ont bien été enregistrées";
        header('Location:'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
        die;
    }

    ?>

<div class="wrap">
    <h1>Welco</h1>

    <?php if(isset($_SESSION["flash_welco"])): ?>
        <div class="success">
            <?= $_SESSION["flash_welco"]; ?>
        </div>
    <?php unset($_SESSION["flash_welco"]); endif; ?>

    <form action="#" method="post" id="form-settings-welco">
        <div>
            <label for="adresse">Nom boutique</label>
            <input type="text" name="boutiquename" value="<?= get_option("boutiquename_welco"); ?>"" id="boutiquename"><br/>
        </div>
        <div>
            <label for="adresse">Adresse</label>
            <input type="text" name="adresse" value="<?= get_option("adresse_welco"); ?>"" id="adresse"><br/>
        </div>
        <div>
            <label for="cp">Code postal</label>
            <input type="text" name="cp" value="<?= get_option("cp_welco"); ?>" id="cp"><br/>
        </div>
        <div>
            <label for="ville">Ville</label>
            <input type="text" name="ville" value="<?= get_option("ville_welco"); ?>" id="ville"><br/>
        </div>
        <div>
            <label for="mail">E-mail</label>
            <input type="text" name="mail" value="<?= get_option("mail_welco"); ?>" id="mail"><br/>
        </div>
        <div>
            <label for="tel">Téléphone</label>
            <input type="text" name="tel" value="<?= get_option("tel_welco"); ?>" id="tel"><br/>
        </div>
        <div>
            <label for="mapboxkey">Clé API Mapbox</label>
            <input type="text" name="mapboxkey" value="<?= get_option("mapboxkey_welco"); ?>" id="mapboxkey"><br/>
        </div>
        <div>
            <label for="welcokey">Clé API Welco</label>
            <input type="text" name="welcokey" value="<?= get_option("welcokey_welco"); ?>" id="welcokey"><br/>
        </div>
        <div>
            <label for="welcoapiurl">URL Api Welco</label>
            <input type="text" name="welcoapiurl" value="<?= get_option("welcoapiurl_welco"); ?>" id="welcoapiurl"><br/>
        </div>
        <div>
            <label for="welcowidgeturl">Url Widget Welco</label>
            <input type="text" name="welcowidgeturl" value="<?= get_option("welcowidgeturl_welco"); ?>" id="welcowidgeturl"><br/>
        </div>
        <div>
            <label for="commandeEnAttenteWelco">État pour commande en attente</label>
            <select name="commandeEnAttenteWelco" id="commandeEnAttenteWelco">
                <option value="wc-attente-welco" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-attente-welco")? "selected": ""; ?>> En attente Welco</option>
                <option value="wc-pending" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-pending")? "selected": ""; ?>> Attente paiement</option>
                <option value="wc-processing" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-processing")? "selected": ""; ?>> En cours</option>
                <option value="wc-livree-welco" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-livree-welco")? "selected": ""; ?>> Commande livrée</option>
                <option value="wc-en-cours-welco" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-en-cours-welco")? "selected": ""; ?>> En cours de préparation</option>
                <option value="wc-expediee-welco" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-expediee-welco")? "selected": ""; ?>> Commande expediée</option>
                <option value="wc-on-hold" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-on-hold")? "selected" : ""; ?>> En attente</option>
                <option value="wc-completed" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-completed")?  "selected" :""; ?>> Terminée</option>
                <option value="wc-cancelled" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-cancelled")?  "selected": ""; ?>> Annulée</option>
                <option value="wc-refunded" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-refunded")?  "selected" : ""; ?>> Remboursée</option>
                <option value="wc-failed" <?= (get_option("commandeEnAttenteWelco_welco") == "wc-failed")?  "selected" : ""; ?>> Échouée</option>
            </select>
        </div>
        <div>
            <label for="commandeStatutPretPourExpedition">État pour prêt pour expedition</label>
            <select name="commandeStatutPretPourExpedition" id="commandeStatutPretPourExpedition">
                <option value="wc-en-cours-welco" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-en-cours-welco") ? "selected" : ""; ?>> En cours de préparation</option>
                <option value="wc-attente-welco" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-attente-welco") ? "selected" : ""; ?>> En attente Welco</option>
                <option value="wc-pending" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-pending") ? "selected" : ""; ?>> Attente paiement</option>
                <option value="wc-processing" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-processing") ? "selected" : ""; ?>> En cours</option>
                <option value="wc-livree-welco" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-livree-welco") ? "selected" : ""; ?>> Commande livrée</option>
                <option value="wc-expediee-welco" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-expediee-welco") ? "selected" : ""; ?>> Commande expediée</option>
                <option value="wc-on-hold" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-on-hold") ? "selected" : ""; ?>> En attente</option>
                <option value="wc-completed" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-completed") ? "selected" : ""; ?>> Terminée</option>
                <option value="wc-cancelled" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-cancelled") ? "selected" : ""; ?>> Annulée</option>
                <option value="wc-refunded" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-refunded") ? "selected" : ""; ?>> Remboursée</option>
                <option value="wc-failed" <?= ( get_option("commandeStatusPretPourExpedition_welco") == "wc-failed") ? "selected" : ""; ?>> Échouée</option>
            </select>
        </div>
        <div>
            <label for="commandeStatutExpediee">État pour expédié</label>
            <select name="commandeStatutExpediee" id="commandeStatutExpediee">
                <option value="wc-expediee-welco" <?= (get_option("commandeStatutExpediee_welco") == "wc-en-cours-welco" )? "selected" : ""; ?>> Commande expediée</option>
                <option value="wc-attente-welco" <?= (get_option("commandeStatutExpediee_welco") == "wc-attente-welco" )? "selected" : ""; ?>> En attente Welco</option>
                <option value="wc-pending" <?= (get_option("commandeStatutExpediee_welco") == "wc-pending" )? "selected" : ""; ?>> Attente paiement</option>
                <option value="wc-processing" <?= (get_option("commandeStatutExpediee_welco") == "wc-processing" )? "selected" : ""; ?>> En cours</option>
                <option value="wc-livree-welco" <?= (get_option("commandeStatutExpediee_welco") == "wc-livree-welco" )? "selected" : ""; ?>> Commande livrée</option>
                <option value="wc-en-cours-welco" <?= (get_option("commandeStatutExpediee_welco") == "wc-expediee-welco" )? "selected" : ""; ?>> En cours de préparation</option>
                <option value="wc-on-hold" <?= (get_option("commandeStatutExpediee_welco") == "wc-on-hold" )? "selected" : ""; ?>> En attente</option>
                <option value="wc-completed" <?= (get_option("commandeStatutExpediee_welco") == "wc-completed" )? "selected" : ""; ?>> Terminée</option>
                <option value="wc-cancelled" <?= (get_option("commandeStatutExpediee_welco") == "wc-cancelled" )? "selected" : ""; ?>> Annulée</option>
                <option value="wc-refunded" <?= (get_option("commandeStatutExpediee_welco") == "wc-refunded" )? "selected" : ""; ?>> Remboursée</option>
                <option value="wc-failed" <?= (get_option("commandeStatutExpediee_welco") == "wc-failed" )? "selected" : ""; ?>> Échouée</option>
            </select>
        </div>
        <div>
            <label for="commandeStatutLivree">État pour livré</label>
            <select name="commandeStatutLivree" id="commandeStatutLivree">
                <option value="wc-livree-welco" <?= (get_option("commandeStatutLivree_welco") == "wc-livree-welco" ) ? "selected" : ""; ?>> Commande livrée</option>
                <option value="wc-attente-welco" <?= (get_option("commandeStatutLivree_welco") == "wc-attente-welco" ) ? "selected" : ""; ?>> En attente Welco</option>
                <option value="wc-pending" <?= (get_option("commandeStatutLivree_welco") == "wc-pending" ) ? "selected" : ""; ?>> Attente paiement</option>
                <option value="wc-processing" <?= (get_option("commandeStatutLivree_welco") == "wc-processing" ) ? "selected" : ""; ?>> En cours</option>
                <option value="wc-en-cours-welco" <?= (get_option("commandeStatutLivree_welco") == "wc-en-cours-welco" ) ? "selected" : ""; ?>> En cours de préparation</option>
                <option value="wc-expediee-welco" <?= (get_option("commandeStatutLivree_welco") == "wc-expediee-welco" ) ? "selected" : ""; ?>> Commande expediée</option>
                <option value="wc-on-hold" <?= (get_option("commandeStatutLivree_welco") == "wc-on-hold" ) ? "selected" : ""; ?>> En attente</option>
                <option value="wc-completed" <?= (get_option("commandeStatutLivree_welco") == "wc-completed" ) ? "selected" : ""; ?>> Terminée</option>
                <option value="wc-cancelled" <?= (get_option("commandeStatutLivree_welco") == "wc-cancelled" ) ? "selected" : ""; ?>> Annulée</option>
                <option value="wc-refunded" <?= (get_option("commandeStatutLivree_welco") == "wc-refunded" ) ? "selected" : ""; ?>> Remboursée</option>
                <option value="wc-failed" <?= (get_option("commandeStatutLivree_welco") == "wc-failed" ) ? "selected" : ""; ?>> Échouée</option>
            </select>
        </div>
        <div>
            <label for="majAutoCron">
                Mise à jour automatique des commandes
            </label>
            <input type="checkbox" name="majAutoCron" value="yes" <?= (get_option("majAutoCron") == "yes" ) ? "checked" : ""; ?>>
        </div>
        <input type="submit" name="valider" value="valider">
    </form>
</div>


<?php } ;?>
