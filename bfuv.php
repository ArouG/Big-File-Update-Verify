<?php
//----------------------------------------
// bfuv :   1.0 : 2022/03/11
//   SHA1 LIMIT2 0 MESSAGE DE 2^64 bit soit 2^56 octets https://fr.wikipedia.org/wiki/SHA-1
//          2.0 : 2022/03/25
//          2.1 : 2022/03/25    envoit un seul bloc !
//          2.1 : 2022/03/26    déplace texte vers le bas pour envoit_bloc
//          2.2 : 2022/03/27    ok pour les procédures avec fichier complet
//----------------------------------------  

/* Création des tables TabInd, metroMNT et corseMNT de la base de données SQLITE
BEGIN TRANSACTION;
DROP TABLE IF EXISTS TabInd;
CREATE TABLE "TabInd" (
    "Xl"    INTEGER,
    "Yl"    INTEGER,
    "Dp"    TEXT,
    "datT"  TEXT,
    "X_Y"   TEXT NOT NULL
);
DROP TABLE IF EXISTS metroMNT;
CREATE TABLE "metroMNT" (
    "Lg"    BLOB,
    "Ylamb" INTEGER NOT NULL UNIQUE,
    PRIMARY KEY("Ylamb")
) WITHOUT ROWID;
DROP TABLE IF EXISTS corseMNT;
CREATE TABLE "corseMNT" (
    "Lg"    BLOB,
    "Ylamb" INTEGER NOT NULL UNIQUE,
    PRIMARY KEY("Ylamb")
) WITHOUT ROWID;
WITH RECURSIVE
   f (n)
AS (
    SELECT 6135005
    UNION ALL
    SELECT n+5 FROM f WHERE n < 7115000
)
INSERT INTO metroMNT SELECT X'80c6', n FROM f;
WITH RECURSIVE
   g (n)
AS (
    SELECT 6045005
    UNION ALL
    SELECT n+5 FROM g WHERE n < 6240000
)
INSERT INTO corseMNT SELECT X'80e6', n FROM g;
COMMIT;
*/

/**************************
 * upload_max_filesize = 2M en général
 * post_max_size = 8M
 */

function file_ecrit($filename,$data)
  {                                  // pour gestion des erreurs ET sauvegarde de compte.txt (ceinture ET bretelles)
    if($fp = fopen($filename,'a'))   // mode ajout !!
    {
      $ok = fwrite($fp,$data);
      fclose($fp);
      return $ok;
    }
    else return false;
  }      
  date_default_timezone_set('Europe/Paris');
  ini_set("always_populate_raw_post_data", "-1");
  $name_sqlite = 'bfuv.sqlite';
  $db = new PDO('sqlite:bfuv.sqlite');
  // ATTENTION à ne pas oublier de CASTER le blob / "hex(hashT)" sous peine de détraquer le fichier HTML
  $pdo_result = $db->query('SELECT rowid, nom, taille, cluster, date, hex(hashT) FROM files');
  $vin = [];
  if (!$pdo_result){
      $sqlreq = 'CREATE TABLE "files" ( "nom" TEXT NOT NULL UNIQUE, "taille" TEXT NOT NULL, "cluster" INTEGER NOT NULL, "date" TEXT NOT NULL, "hashT" BLOB)';   
      $db->exec($sqlreq); 
      $db->exec($sqlreq);
  } else {
      $pdo_result->setFetchMode(PDO::FETCH_OBJ);
      $vin = $pdo_result->fetchAll();      
      $pdo_result->closeCursor(); 
  } 
  $db = Null;
  /* dans tous les cas, on part avec une base de données prête à être exploitée okazou !. */
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="cache-control" content="no-cache, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta name="DC.Language" content="fr" />
        <meta name="description" content="Reparetrace" />
        <meta name="author" content="ArouG" />
        <meta name="keywords" content="constitution et entretien MNT 5*5" />
        <meta name="date" content="2022/01/29" />
        <meta name="robots" content="nofollow" />
        <title>Upload de gros fichiers</title>
        <style>
        html {
            font: 1.1em sans-serif;
        }
        
        body {
            display: block;
            background-color: black;
            margin: 8px;
        }
        
        .top-box {
            width: 1400px;
            height: 18px;
            margin-bottom: -18px;
            position: relative;
        }
        
        #bidon {
            width: 1400px;
            height: 20px;
            background-color: #000000;
            float: left;
        }
        
        #entete {
            width: 1400px;
            margin-top: 0;
            margin-bottom: 0;
            margin-left: 0;
            margin-right: 0;
            line-height: 5px;
            background-color: #600c0c;
            padding-top: 0;
            z-index: 50;
        }
        
        #entete p {
            color: #f0e39e;
            font-family: Georgia, "Bitstream Vera Serif", Norasi, serif;
            font-size: 0.8em;
            font-style: italic;
            line-height: 0.2em;
        }
        
        #cornleft {
            float: left;
            width: 150px;
            height: 75px;
            position: relative;
            background-color: #600c0c;
        }
        
        #titre {
            float: left;
            width: 1100px;
            position: relative;
            margin-top: 0;
            height: 75px;
            background-color: #600c0c;
        }
        
        #titre h2 {
            color: #f0e39e;
            font-family: Georgia, "Bitstream Vera Serif", Norasi, serif;
            font-style: italic;
            font-size: 1.1em;
            font-style: italic;
            text-align: center;
        }
        
        #cornright {
            float: left;
            width: 150px;
            height: 75px;
            position: relative;
            background-color: #600c0c;
        }
        
        #menu {
            text-align: center;
            background-color: #FFDEAD;
            width: 1400px;
            margin: auto;
            padding: 0;
        }
        
        #basdepage {
            margin: 0;
            padding: 0;
            font-size: 0.55em;
            background-color: #600c0c;
            width: 1400px;
            float: left;
        }
        
        #gauche {
            text-align: left;
            float: left;
        }
        
        #droite {
            text-align: right;
            float: left;
        }
        
        #centrebas {
            float: left;
            width: 1224px;
            text-align: center;
            margin: auto;
            padding: 0;
            color: white;
            font-family: Georgia, "Bitstream Vera Serif", Norasi, serif;
            font-style: italic;
            font-size: 18px;
            font-style: italic;
        }
        
        #progressDesc {
            visibility: hidden;
        }
        
        #pgrbar {
            visibility: hidden;
        }
        </style>
        <script type="text/javascript">
        <?php echo 'var rep=' . json_encode($vin) . ";\n" ?>
        let main = {};
        let intervalId;
        let currentBar = 0;
        const nohash = 'Oooops!_no_data_hash';
        let RowId;

        function fichier_en_table_files(nom, taille, date) {
            let find = false;
            for(var i = 0; i < rep.length; i++) {
                if((rep[i]['nom'] == nom) && (rep[i]['taille'] == taille) && (rep[i]['date'] == date)) {
                    find = true;
                }
            }
            return find;
        }

        function indof(nom, taille, date) {
            for(var i = 0; i < rep.length; i++) {
                if((rep[i]['nom'] == nom) && (rep[i]['taille'] == taille) && (rep[i]['date'] == date)) {
                    //ok = i;
                    //break;
                    return i;
                }
            }
        }
        async function init_row(nom, taille, date, cluster) { // lancé que si le fichier n'est pas déjà en table
            let url = './receive.php?N=' + encodeURIComponent(nom) + "&S=" + taille + "&D=" + encodeURIComponent(date) + "&C=" + cluster;
            let reponse = await fetch(url, {
                method: 'GET'
            });
            if(reponse.status == 200) {
                let rep = await reponse.json();
                rowid = parseInt(rep['RId']);
                if(rowid > 0) {
                    document.querySelector('#outt').textContent += " : line number " + rowid + " \n";
                    return rowid;
                    // on continue
                } else {
                    // pas normal !!
                    document.querySelector('#outt').textContent += " : problem, retour = " + rowid + "\n";
                    return rowid;
                }
            } else {
                document.querySelector('#outt').textContent += " : problem, status = " + reponse.status + "\n";
                return reponse.status;
            }
        }
        /***************************************************
         * Concaténation de Blob   var myBlobBuilder = new MyBlobBuilder();
         **************************************************/
        var MyBlobBuilder = function() {
            this.parts = [];
        }
        MyBlobBuilder.prototype.append = function(part) {
            this.parts.push(part);
            this.blob = undefined; // Invalidate the blob
        };
        MyBlobBuilder.prototype.getBlob = function() {
            if(!this.blob) {
                //this.blob = new Blob(this.parts, { type: "text/plain" });
                this.blob = new Blob(this.parts, {
                    type: "binary"
                });
            }
            return this.blob;
        };
        async function part_envoi_data(str, buff) {
            var myBlobBuilder = new MyBlobBuilder();
            // concaténation pour avoir un blob formé de 2
            myBlobBuilder.append(str);
            myBlobBuilder.append(buff);
            var newdata = myBlobBuilder.getBlob();
            const settings = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: newdata
            };
            var url = './receive.php';
            try {
                let response = await fetch(url, settings); // se résout avec des en-têtes de réponse
                let result = await response.text();
                return result;
            } catch(e) {
                return e;
            }
        }

        async function AsyncReadBytes(offset, nbB) {
            if((offset + nbB <= main.file.size) && (offset >= 0) && (nbB >= 0)) {
                try {
                    var partie = main.file.slice(offset, offset + nbB);
                    var tmpblob = new Response(partie);
                    var buffer = await tmpblob.arrayBuffer();
                    return new DataView(buffer);
                } catch {
                    console.log('error AsyncReadBytes');
                    return false;
                }
            } else {
                console.log('Buffer impossible');
                return false;
            }
        }

        async function tailleFSv(fname) {
            let url = './receive.php?N=' + encodeURIComponent(fname) + "&I";
            let reponse = await fetch(url, {
                method: 'GET'
            });
            if(reponse.status == 200) {
                let rep = await reponse.json();
                return rep;
            } else {
                document.querySelector('#outt').textContent += " : problem during tailleFSv, status = " + reponse.status + "\n";
                return reponse.status;
            }
        }

        function buf2hex(buffer) { // buffer is an ArrayBuffer
            return [...new Uint8Array(buffer)].map(x => x.toString(16).padStart(2, '0')).join('');
        }

        function buf2str(arrays) {
            var tab = new Uint8Array(arrays);
            result = '';
            for(var i = 0; i < tab.length; i++) result += String.fromCharCode(tab[i]);
            return result;
        }

        function human_date(fdate) {
            var d = new Date(fdate);
            var dattext = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + '=' + d.getHours() + ':' + d.getMinutes();
            return dattext;
        }

        function Cluster(taille) {
            if(taille <= Math.pow(2, 26)) { // 64 Mo
                return 1;
            }
            if(taille <= Math.pow(2, 30)) { // 256 Mo
                return 2;
            }
            if(taille <= Math.pow(2, 34)) { // 1 Go
                return 4;
            }
            if(taille <= Math.pow(2, 38)) { // 4 Go
                return 8;
            }
            if(taille <= Math.pow(2, 42)) { // 16 Go
                return 16;
            }
            if(taille <= Math.pow(2, 46)) { // 64 Go
                return 32;
            }
            if(taille <= Math.pow(2, 50)) { // 256 Go
                return 64;
            }
            if(taille <= Math.pow(2, 54)) { // 1024 Go
                return 128;
            }
            return 256;
        }
        async function fetchPostB(str) {
            const settings = {
                method: 'POST',
                headers: {
                    //'Content-Type': 'application/octet-stream'
                    'Content-Type': 'text/plain'
                },
                body: str
            };
            var url = './receive.php';
            try {
                let response = await fetch(url, settings); // se résout avec des en-têtes de réponse
                let result = await response.text(); // en chaine hexa
                return result;
            } catch(e) {
                return e;
            }
        }
        async function envoit_bloc(i, hashBlock) {
            // Penser à déterminer taille totale du bloc d'abord (normalement Cluster sauf dernier)
            var ind;
            var ret_nb_octs;
            var offset;
            var lastbuffSize;
            var total = 100;
            var sizecluster = Math.pow(2, 20) * main.clust;
            var offsetbase = i * sizecluster;
            if((main.file.size - offsetbase) <= sizecluster) {
                sizecluster = main.file.size - offsetbase;
            }
            // sizecluster prend la taille du dernier cluster éventuellement !
            if(sizecluster < 1048576) {
                // reste moins d'un Mo à envoyer =>  d'un seul bloc
                document.querySelector('#outt').textContent += 'Un seul petit bloc de taille = ' + sizecluster + "\n";
                //considère que num partie = 99
                offset = offsetbase;
                lastbuffSize = sizecluster;
                var buff = await AsyncReadBytes(offset, lastbuffSize);
                var DataToSend = main.RowId.toString(10) + '-' + "cremod_" + i + "_100_"+ hashBlock +'_' + buff.byteLength + "*";
                ret_nb_octs = await part_envoi_data(DataToSend, buff);
                document.querySelector('#outt').textContent += 'Partie 100/100 : clef hash = ' + ret_nb_octs + "\n";
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                return ret_nb_octs;
            } else {
                var buffSize = Math.floor(sizecluster / 100);
                lastbuffSize = 0;
                if(sizecluster != 100 * buffSize) {
                    buffSize += 1;
                    lastbuffSize = sizecluster - (99 * buffSize);
                }
                document.querySelector('#outt').textContent += 'Nouveau bloc offset <' + offsetbase + '> prêt. Il devrait avoir ' + sizecluster + " octets\n";
                for(ind = 0; ind < 99; ind++) {
                    offset = offsetbase + (ind * buffSize);
                    var buff = await AsyncReadBytes(offset, buffSize);
                    var DataToSend = main.RowId.toString(10) + '-' + "cremod_" + i + "_" + ind + "_" + hashBlock +'_' + buff.byteLength + "*";
                    //var firstpart = DataToSend + buf2str(buff);
                    ret_nb_octs = await part_envoi_data(DataToSend, buff);
                    document.querySelector('#outt').textContent += 'Partie ' + (ind + 1) + '/100 : comporte ' + ret_nb_octs + " octets\n";
                    document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                }
                // ind == 99 (100ième envoi)
                offset = offsetbase + (ind * buffSize);
                var buff = await AsyncReadBytes(offset, lastbuffSize);
                var DataToSend = main.RowId.toString(10) + '-' + "cremod_" + i + "_" + ind + "_" +  hashBlock +'_' + buff.byteLength + "*";
                ret_nb_octs = await part_envoi_data(DataToSend, buff);
                document.querySelector('#outt').textContent += 'Partie ' + (ind + 1) + '/100 : clef hash = ' + ret_nb_octs + "\n";
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                return ret_nb_octs;
            }
        }
        async function return_hash_table(file, idx) {
            let fname = file.name;
            let fsize = file.size;
            let fdate = file.lastModified;
            var clust = Cluster(file.size);
            main.clust = clust;
            main.fsizeSv = await tailleFSv(fname);
            main.diff = 0;
            // enregistre dans BdD et récupère le rowId correspondant
            if(idx == 0) {
                var RowId = await init_row(fname, fsize, human_date(fdate), clust);
                if(RowId < 0) {
                    RowId = -RowId; // la création de fichier ne s'est pas passé comme il faut !? OU il n'existait pas !
                }
            } else {
                RowId = idx;
                document.querySelector('#outt').textContent += " : line number " + RowId + " \n";
            }
            // création hash Table
            main.HexHash = [];
            main.RowId = RowId;
            var tabHash = '';
            var buff;
            var nbB = clust * Math.pow(2, 20);
            var nbloop = Math.floor(file.size / nbB);
            var rest = file.size % nbB;
            var adeq = 0;
            if(rest > 0) adeq = 1;
            var offset = 0;
            main.HexHashS = [];
            document.querySelector('#progressDesc').textContent = "we build the hash table";
            intervalId = setInterval(displayBar, 100); // dysplayBar est appelée toutes les 100 millisecondes
            init_progress(0, nbloop + adeq);
            document.querySelector('#progressDesc').style.visibility = "visible";
            document.querySelector('#pgrbar').style.visibility = "visible";
            for(var i = 0; i < nbloop; i++) {
                buff = await AsyncReadBytes(offset, nbB);
                tmp = await crypto.subtle.digest('SHA-1', buff); //
                main.HexHash.push(buf2hex(tmp));
                tabHash += buf2str(tmp); //console.log('longueur hash = '+tabHash.length);
                document.querySelector('#outt').textContent += "Client : " + buf2hex(tmp) + "\n";
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                offset += nbB;
                // old
                var DataToSend = RowId.toString(10) + '-' + "hashSv_" + i + "_" + nbB + "_" + buf2hex(tmp) + "_O*"; // ou GET ?
                var firstpart = DataToSend + main.HexHash.join('');
                var ret = await fetchPostB(firstpart);
                main.HexHashS.push(ret);
                document.querySelector('#outt').textContent += "Server : " + ret + "\n";
                if(ret != buf2hex(tmp)) {
                    main.diff += 1;
                    document.querySelector('#outt').textContent += '---------error---------' + "\n";
                }
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                //barre de progression
                currentBar = i;
                displayBar();
            }
            if(rest > 0) {
                buff = await AsyncReadBytes(offset, rest);
                tmp = await crypto.subtle.digest('SHA-1', buff); //
                //tabHash += buf2str(tmp)+nohash;
                tabHash += buf2str(tmp); //console.log('longueur hash = '+tabHash.length);
                document.querySelector('#outt').textContent += "Client : " + buf2hex(tmp) + "\n";
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                main.HexHash.push(buf2hex(tmp));
                //document.getElementById("progressBar").value += adeq; 
                // old
                var DataToSend = RowId.toString(10) + '-' + "hashSv_" + i + "_" + rest + "_" + buf2hex(tmp) + "_O*"; // ou GET ?
                var firstpart = DataToSend + main.HexHash.join('');
                var ret = await fetchPostB(firstpart);
                main.HexHashS.push(ret);
                document.querySelector('#outt').textContent += "Server : " + ret + "\n";
                if(ret != buf2hex(tmp)) {
                    main.diff += 1;
                    document.querySelector('#outt').textContent += '---------error---------' + "\n";
                }
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
            }
            document.querySelector('#outt').textContent += "\n\n" + "l'analyse a révélé : " + main.diff + " 'blocs' différents\n";
            document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
            main.tabHash = tabHash;
            //barre de progression
            currentBar = 100;
            displayBar();
            clearInterval(intervalId);
            document.querySelector('#progressDesc').style.visibility = "hidden";
            document.querySelector('#pgrbar').style.visibility = "hidden";
            main.tabHash = tabHash; // 30940 octets !?
            i = 0;
            while(i < main.HexHash.length) {
                if(main.HexHash[i] != main.HexHashS[i]) {
                    document.querySelector('#outt').textContent += "Envoit bloc " + i + '/' + main.HexHash.length + "\n";
                    main.HexHashS[i] = await envoit_bloc(i, main.HexHash[i]);  // format hex
                }
                i += 1;
            }
            var tabinSv = [];
            for (i=0; i<main.HexHash.length; i++){
                tabinSv.push([main.HexHash[i], main.HexHashS[i]]);    
            }
            asyncFunc(tabinSv);
            var truc = 3;
        }

        function init_progress(currentBarv, currentBarmax) {
            progressBar = document.getElementById("progressBar");
            progressBar.value = currentBarv;
            progressBar.max = currentBarmax;
        }
        let displayBar = function() {
            progressBar.value = currentBar;
        }
        async function asyncFunc(tabinSv){
            var buff;
            var nbB = main.clust * Math.pow(2, 20);
            var nbloop = Math.floor(main.file.size / nbB);
            var rest = main.file.size % nbB;
            var adeq = 0;
            if(rest > 0) adeq = 1;
            var Clhash, Svhash;
            main.fsizeSv = await tailleFSv(main.fname);

                        i = 0;
                        while((i < tabinSv.length)) {
                            if(tabinSv[i][0] != tabinSv[i][1]) {
                                document.querySelector('#outt').textContent += "Envoit bloc " + i + '/' + tabinSv.length + "\n";
                                var tmp = await envoit_bloc(i, tabinSv[i][0]);   // format hex puisque 40 octets
                            }
                            i += 1;
                        }
            document.querySelector('#outt').textContent += "\n\nTous les blocs différents ont été mis à jour au niveau de la table de correspondance des blocs.\nDernière vérification entre la table et les deux fichiers avant de clore le sujet :\n\n";
            i=0;
            var offset = 0;
            var badSvCluster = [];
            var badClCluster = [];
            while (i<nbloop) {
                buff = await AsyncReadBytes(offset, nbB);
                tmp = await crypto.subtle.digest('SHA-1', buff); 

                var DataToSend = main.RowId.toString(10) + '-' + "hashSv_" + i + "_" + nbB + "_" + buf2hex(tmp) + "_N*"; // ou GET ?
                Svhash = await fetchPostB(DataToSend);

                if (tabinSv[i][0].toLowerCase() == buf2hex(tmp)){
                    document.querySelector('#outt').textContent += "Fichier Client, le bloc n°"+(i+1)+" correspond bien\n";
                } else {
                    badClCluster.push(i);   
                    document.querySelector('#outt').textContent += "Fichier Client, le bloc n°"+(i+1)+" ne correspond pas\n"; 
                }
                if (tabinSv[i][1].toLowerCase() == Svhash){
                    document.querySelector('#outt').textContent += "Fichier Serveur, le bloc n°"+(i+1)+" correspond bien\n";
                } else {
                    badSvCluster.push(i);   
                    document.querySelector('#outt').textContent += "Fichier Serveur, le bloc n°"+(i+1)+" ne correspond pas\n"; 
                }
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                offset += nbB;
                i += 1;
            }     
            if (rest > 0) {
                buff = await AsyncReadBytes(offset, rest);
                tmp = await crypto.subtle.digest('SHA-1', buff); //
                var DataToSend = main.RowId.toString(10) + '-' + "hashSv_" + nbloop + "_" + rest + "_" + buf2hex(tmp) + "_N*"; // ou GET ?
                Svhash = await fetchPostB(DataToSend);
                if (tabinSv[i][0].toLowerCase() == buf2hex(tmp)){
                    document.querySelector('#outt').textContent += "Fichier Client, le bloc n°"+(i+1)+" correspond bien\n";
                } else {
                    badClCluster.push(i);   
                    document.querySelector('#outt').textContent += "Fichier Client, le bloc n°"+(i+1)+" ne correspond pas\n"; 
                }
                if (tabinSv[i][1].toLowerCase() == Svhash){
                    document.querySelector('#outt').textContent += "Fichier Serveur, le bloc n°"+(i+1)+" correspond bien\n";
                } else {
                    badSvCluster.push(i);   
                    document.querySelector('#outt').textContent += "Fichier Serveur, le bloc n°"+(i+1)+" ne correspond pas\n"; 
                }
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
            }        
            if (badClCluster.length + badSvCluster.length == 0){
                document.querySelector('#outt').textContent += "\n\nLes fichiers sont bien identiques. La table va être effacée. La voici avant effacement :\n";
                for (i=0; i<tabinSv.length; i++){
                    document.querySelector('#outt').textContent += "Bloc n°"+(i+1)+", clef SHA1 = " + tabinSv[i][0].toLowerCase()+ "\n";    
                }
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
                // effacement de la ligne en table
                var DataToSend = main.RowId.toString(10) + '-' + "delete_" + i + "*";
                var nbLinesDeleted = await part_envoi_data(DataToSend, buff);
                if (nbLinesDeleted == 1){
                    document.querySelector('#outt').textContent += "\n\nLa ligne de ce fichier a bien été effacée de la table ! \n"; 
                } else {
                    document.querySelector('#outt').textContent += "\n\nProblème d'effacement en table (opérez en manuel SVP). \n";
                }
                document.querySelector('#outt').scrollTo(0, document.querySelector('#outt').scrollHeight);
            } else {
                document.querySelector('#outt').textContent += "Il reste des blocs différents ... Relancez SVP !\n"; 
            }

        }                

        async function init() {
            document.querySelector('#inp').onchange = function(e) {
                let fichiersInput = document.querySelector("#inp");
                let fichier = fichiersInput.files[0];
                main.file = fichier;
                main.clust = 1; // par défaut
                let fname = fichier.name;
                let fsize = fichier.size;
                let fdate = fichier.lastModified;
                var dattext = human_date(fdate);
                if(!fichier_en_table_files(fname, fsize, dattext)) {
                    // Considère que c'est la toute première fois
                    // initialise nouveau process
                    document.querySelector('#outt').textContent += "Begin a new process with " + fname;
                    document.querySelector('#textinp').textContent = 'file choosed = ' + fname;
                    return_hash_table(fichier, 0);   // on continue
                } else {
                    var indRep = indof(fname, fsize, dattext);
                    main.RowId = rep[indRep].rowid;
                    if ((rep[indRep]['hex(hashT)'] === undefined) || (rep[indRep]['hex(hashT)'] === '')){
                        // poursuit ... ou valide !
                        document.querySelector('#outt').textContent += "Continue the process with " + fname;
                        return_hash_table(fichier, rep[indRep]['rowid']);
                    } else {                                   // fichier en BdD et avec hashT
                        clust = rep[indRep]['cluster'];
                        main.clust = clust;
                        nbB = clust * Math.pow(2, 20);
                        nbloop = Math.floor(fsize / nbB);
                        rest = fsize % nbB;
                        adeq = 0;
                        if(rest > 0) adeq = 1;
                        hashSv = rep[indRep]['hex(hashT)'];
                        nbclust = hashSv.length / 40;
                        tabinSv = [];
                        for(i = 0; i < nbloop; i++) {
                            tabinSv.push([hashSv.substring(80 * i, (80 * i) + 40), hashSv.substring((80 * i) + 40, 80 * (i + 1))]);
                        }
                        if(adeq == 1) {
                            tabinSv.push([hashSv.substring(80 * nbloop, (80 * nbloop) + 40), hashSv.substring((80 * nbloop) + 40, 80 * (nbloop + 1))]);
                        }
                        //main.tabinSv = tabinSv;
                        asyncFunc(tabinSv);
                    }
                }
            }
        }
        </script>
    </head>

    <body onload="init();">
        <div id="menu">
            <div id="top_of_box" class="top-box"> </div>
            <div id="bidon"></div>
            <div id="entete"> <img id="cornleft" src="data:image/gif;base64,R0lGODlhlgBLAOfLABEMCw4PBBUNBAwPDCkJBTEJBBwQChkTDBUVDCATCCcRChgWBzAQBx8UFiIXEx4aBhgbDygZChkcGiMfDR8gGRspED4fEColEx0qCygmGy8lFCQoGy4oDigqDjkmETMoECkqKRgxGT0oDBsyEiMwEkwlDDEsGSYyDiM3EU4rEkktHCQ6GkEzEDk1GiI7JDQ3ITo2JDg2KkI1Fyo7I0ozFj04EUA0MDw4GB5AJTE8FjU4Mh5CGB5KGiZIHyBKIFI8JStIK0dCIV88GBxQJDNKIDdIKk1BM1FCH1hAIDtJIyNPMEdFLE1GGFdDGj1LGUFGQEVGNiZRLCZTIFpFLVBKTCZbIylcHT9VKSFdOFtOKW1LHi5cMGJNM1NTKlFVHjFeJklYIVNSQWVQIllSOl9RNVRSTFRUOVlVISxjKWBWFlFZMm9VGixrMTRqMDtoMjloO0lmMmxeK21dPmdhKnVeKDdyMDNzOHFjImZiTTtxOXthI0JwPGZoJGZlQ4RgJW5kRWFrPGlpYEV7QU15Pzx+Q3trTD9/O3hvOYRtOHdvT5JrLoluLXBxWUGDOJBuJYJzJYxyJnl1S59zLUeMS5Z5MJR6OJF9K5l7K4l/OY18U4B/Ypx9JY58YYSBXIt/XIaCVEuXUY+JMoSGgaiHKJ2JOKeHMKGKJoyMeoSXTZeRboyUc5iVTZeUZpqYWbSWNqmaQrCeOrCfMaKhSLucMqGigaSjcqaoZ7CnX8SqTKqrmaerp7KzgcmzQrO1lsC6WMLEpsrGfMvKjuHOMs7OocvNxNPUt+Tn4vDtsPn0rvLz0vf98v///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAP8ALAAAAACWAEsAAAj+AH2g4EGQ4A4eO3ysWLHDBJEeX4gQkfKlokUiX9QQGjSp4xslFzJkMJFBQYELJEiscAHkxQslW7DI3AJkxYyUM2Zs2aIEiJKUK0wwULDC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" alt="coingauche" />
                <div id="titre">
                    <h2><a>Big File Update & Verify</a></h2>
                    <p style="text-align:center;">--------------------------------</p>
                <p style="text-align:center;">V2.2 du 27/03/2022 - Contact : ArouG at turbosudoku dot fr</p>
                    <hr /> </div> <img id="cornright" src="data:image/gif;base64,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" alt="coindroit" />
                <div><span class="bidon0">&nbsp;</span></div>
            </div>
            <div>
                <div id="choix">
                    <p id="textinp">Choose (again ?) your file to be uploaded :
                        <input id="inp" type="file"> </p>
                </div>
                <br />
                <p id="progressDesc"></p>
                <p id="pgrbar">
                    <progress id='progressBar'></progress>
                </p>
                <textarea id="outt" rows="20" cols="100"></textarea>
            </div>
            <div id="basdepage">
                <hr />
                <div id="gauche">
                    <a href="https://validator.w3.org/check?uri=https://aroug.eu/reparetrace/gere_mnt.php"> <img src="https://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" height="31" width="88" /> </a>
                </div>
                <div id="centrebas">Document soumis à licence <a href="https://creativecommons.org/licenses/by/2.0/fr/">Creative Commons "by"</a></div>
                <div id="droite">
                    <a href="https://jigsaw.w3.org/css-validator/validator?uri=https://aroug.eu/reparetrace/gere_mnt.php"> <img style="border:0;width:88px;height:31px" src="https://jigsaw.w3.org/css-validator/images/vcss" alt="CSS Valide !" /> </a>
                </div>
            </div>
            <form style="display:none;" name="log_out" id="logout" action="bfuv.php" method="post">
                <input type="hidden" name="username" value="toto" />
                <input type="submit" value="log out" /> </form>
        </div>
    </body>

    </html>