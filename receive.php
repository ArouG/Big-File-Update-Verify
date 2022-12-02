<?php
//----------------------------------------
// receive :   1.0 du 11/03/2022
//             2.1 du 25/03/2022  
//             2.2 du 02/12/2022   ligne 283 remplace r+ par c+b
// -------- 2 formes d'appel ----------------
//  1 GET :
//      receive.php?N=<nom>&D=<date>&S=<taille>&C=<taille_Chunk> 32 (Mo par défaut)
//      retour : Ok
//  2 POST :
//      receive.php  B = blob, H = Hash (2 octets), O : Offset, C : nombre octets
//      retour H
//----------------------------------------
set_time_limit(0);
date_default_timezone_set('Europe/Paris');
ini_set("memory_limit", "-1");
ini_set("always_populate_raw_post_data", "-1");
$nohash = 'Oooops!_no_data_hash';

function file_ecrit($filename, $data)
{ // pour gestion des erreurs 
    if ($fp = fopen($filename, 'a')) // mode ajout !!
    
    {
        $ok = fwrite($fp, $data);
        fclose($fp);
        return $ok;
    }
    else return false;
}

function write_file($filename, $data)
{
    if ($fo = fopen($filename, 'w')) // Warning: fwrite() expects parameter 2 to be string, array given
    
    {
        $ok = fwrite($fo, $data);
        fclose($fo);
        return $ok;
    }
    else return false;
}

function modif_fichier($filename, $data)
{ // pour modification fichier
    if ($fa = fopen($filename, 'r+')) // Warning: fwrite() expects parameter 2 to be string, array given
    
    {
        $ok = fwrite($fa, $data);
        fclose($fa);
        return $ok;
    }
    else return false;
}

function hash_Sv($blob)
{
    // petite verification blob esr en octets donc 20 octets par sha1
    if ((strlen($blob) % 40) != 0)
    {
        return -1;
    }
    $table = [];
    $nbrow = strlen($blob) / 40;
    for ($i = 0;$i < $nbrow;$i++)
    {
        $table[] = array(
            "Cl" => substr($blob, 40 * $i, 20) ,
            "Sv" => substr($blob, (40 * $i) + 20, 20)
        );
    }
    return $table;
}

function hash_in($blob)
{
    // petite verification
    if (((strlen($blob) % 20) != 0) && (strlen($blob) > 0))
    {
        return -1;
    }
    $table = [];
    $nbrow = strlen($blob) / 20;
    //file_ecrit('receive_deb.txt','83: '.$nbrow."\n");
    for ($i = 0;$i < $nbrow;$i++)
    {
        $table[] = substr($blob, 20 * $i, 20);
        //file_ecrit('receive_deb.txt','87: '.substr($blob, 20*$i, 20)."\n");
        
    }
    return $table;
}

function nhex2bin($h)
{
    if (!is_string($h)) return null;
    $r = '';
    for ($a = 0;$a < strlen($h);$a += 2)
    {
        $r .= chr(hexdec($h[$a] . $h[($a + 1) ]));
    }
    return $r;
}

//$data = json_decode(file_get_contents('php://input'), true);
// DONNEE POST : INPUT_POST
$input = fopen("php://input", "rb");
$input_post = '';
while (!feof($input))
{
    $input_post .= fread($input, 8192);
}
fclose($input);
//file_ecrit('receive_deb.txt','113: taille input = '.strlen($input_post)."\n");
$db = new PDO('sqlite:bfuv.sqlite');

// si forme GET :
/*--------------------------------------------------*/
if (strlen($input_post) == 0)
{
    $N = '';
    if (isset($_GET['N']))
    {
        $N = $_GET['N'];
    }

    $D = 0;
    if (isset($_GET['D']))
    {
        $D = $_GET['D'];
    }

    $S = 0;
    if (isset($_GET['S']))
    {
        $S = $_GET['S'];
    }

    $C = 0; // taille en Mo <=> 1.048.576 octets
    if (isset($_GET['C']))
    {
        $C = intval($_GET['C']);
    }

    $I = false;
    if (isset($_GET['I']))
    {
        $I = true;
    }

    $retour_GET = array(
        'RId' => - 1,
        'Ht' => '',
        'new' => true
    );
    // Traitement pour l'appel en GET
    if (is_string($N) && is_string($D) && is_string($S) && is_int($C))
    {
        // vérification si dejà existant : on compte combien y'en a
        $reqsel = "SELECT ROWID FROM files WHERE (nom = '" . $N . "') AND (taille = '" . $S . "') AND (date = '" . $D . "') AND (cluster = " . $C . ")";
        //file_ecrit('receive_deb.txt','160 : '.$reqsel."\n");
        $stmt = $db->query($reqsel);
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();
        if (empty($rows))
        {
            // on crée la ligne
            $debug = "N=" . $N . ",S=" . $S . ",C=" . $C . ",D=" . $D . "\n";
            $sqlcreate = "INSERT INTO files (nom, taille, cluster, date) VALUES(?, ?, ?, ?)";
            $stmt = $db->prepare($sqlcreate);
            $stmt->bindParam(1, $N, PDO::PARAM_STR);
            $stmt->bindParam(2, $S, PDO::PARAM_STR);
            $stmt->bindParam(3, $C, PDO::PARAM_INT);
            $stmt->bindParam(4, $D, PDO::PARAM_STR);
            $stmt->execute();
            // retourne numéro de ligne correspondant à l'introduction éventuellement négatif si l'on ne peut créer le fichier (précaution)
            $ret = write_file($N, '');
            if (!file_exists($N)) $retour_GET['RId'] = - $retour_GET['RId']; // rowid négatif <=> pas pu créer fichier
            $retour_GET['RId'] = $db->lastInsertId();
        }
        else
        {
            // ligne déjà existante a priori impossible car le client ne l'a pas récupérée à l'initialisation
            //file_ecrit('receive_deb.txt',"184: EXISTE, ligne ".$res."\n");
            $retour_GET['new'] = false;
            $retour_GET['RId'] = $rows[0]->rowid;
        }
        $tableH = '';
        $fileSv = './' . $N;
        //file_ecrit('receive_deb.txt',"190: OK".json_encode($retour_GET)."\n");
        echo json_encode($retour_GET);
    }
    else
    // y'a un blèm' !
    
    {
        if ($I && is_string($N)){   // demande taille $N côté serveur
            if (!file_exists($N)){  // si fichier inexistant, on le crée avec une taille de 0
                file_ecrit($N,'');
                echo 0;
            } else {
                $fp = fopen($N, 'rb');
                $tsize = (float) 0;
                $tchunksize = 1024 * 1024;
                while (!feof($fp)) {
                    fread($fp, $tchunksize);
                    $tsize += (float) $tchunksize;
                }
                echo $tsize;
            }
        }
        else
        {
            echo json_encode($retour_GET);
            //file_ecrit('receive_deb.txt',"215: bizz\n");
        }
    }
    $db = Null;
}
else
{
    // $input non vide : POST
    $ind = strpos($input_post, "*");
    $firstpart = substr($input_post, 0, $ind);
    //file_ecrit('receive_deb.txt','225: '.$firstpart."\n");
    $params = explode("-", $firstpart);
    $rowId = intval($params[0]);
    $name = $params[1];
    //file_ecrit('receive_deb.txt','229: rowid='.$rowId." et name=".$name."\n");
    if (substr($name, 0, 6) == 'cremod')
    { // envoit d'une partie d'un cluster (gros chunk)
        $params = explode("_", $name);
        $ieme = intval($params[1]);
        $clustInd = intval($params[2]);
        $hashBlCl = $params[3];
        $buffsize = intval($params[4]);
        //file_ecrit('receive_deb.txt','237: cremod=('.$ieme.",".$clustInd.",".$buffsize."\n");
        $reqsel = "SELECT nom, taille, cluster, hex(hashT) AS HhashT FROM files WHERE rowid = " . $rowId;
        $pdo_result = $db->query($reqsel);
        $pdo_result->setFetchMode(PDO::FETCH_BOTH);
        $rows = $pdo_result->fetchAll();
        $pdo_result->closeCursor();
        $namef = $rows[0]['nom'];
        $sizef = bcadd("0", $rows[0]['taille']);
        $clusterf = 1048576 * $rows[0]['cluster'];
        $locHashT = $rows[0]['HhashT'];        // eventuellement vide 
        $tmpfile = $namef . "." . $ieme;
        $data = substr($input_post, $ind + 1);
        if (strlen($data) == $buffsize)
        {
            if (($clustInd % 100) == 0) 
            {
                $ret = write_file($tmpfile, $data);
            }
            else
            {
                $ret = file_ecrit($tmpfile, $data);
            }
            //file_ecrit('receive_deb.txt','259: ret='.$ret."\n");
            if ($ret == $buffsize)
            {
                if ($clustInd < 99)
                {
                    echo $ret; // retourne nombre d'octets traités et fin
                    
                }
                else
                {
                    //file_ecrit('receive_deb.txt','269: hexHashTable= '.$locHashT."\n");
                    // 100 ième envoi (ou premier et unique)
                    $blocHash = sha1_file($tmpfile);
                    //file_ecrit('receive_deb.txt','272: blocHash= '.$blocHash."\n");
                    if ($hashBlCl != $blocHash)
                    { // Aïe !
                        @unlink($tmpfile); // on efface le fichier
                        echo "fault";
                    }
                    else
                    {
                        // OK on a bien le bon bloc ! on l'écrit / remplace dans notre fichier
                        // positionnons-nous bien au bon endroit ! suppose que taille est au moins égale à ... normalement oui !
                        $f2mod = fopen($namef, "c+b");                      //maj   c création b pour mode binaire
                        for ($k = 0;$k < $ieme;$k++)
                        {
                            $nimp = fread($f2mod, $clusterf);
                        }
                        $fread = fopen($tmpfile, "r");
                        $data_block = fread($fread, filesize($tmpfile));
                        fwrite($f2mod, $data_block);
                        //mise à jour table
                        $newhash = substr($locHashT, 0, (80 * $ieme)+40) . $blocHash . substr($locHashT, 80 * ($ieme+1));
                        $requpdate = "UPDATE files SET hashT = X'" . $newhash . "' WHERE rowid = " . $rowId;
                        $db->beginTransaction();
                        $stmt = $db->exec($requpdate);
                        $db->commit();
                        echo $blocHash; // dernière passe : envoit le hash
                        fclose($f2mod);
                        fclose($fread);
                        unlink($tmpfile);                              
                    }
                }
            } // else {file_ecrit('receive_deb.txt',"303: nombre octets ecrits dans fichier temp != buffsize\n");}
        } //else {file_ecrit('receive_deb.txt',"304: nombre octets envoyés dans fichier temp != buffsize\n");}
    }
    if (substr($name, 0, 6) == 'hashSv')
    {
        $params = explode("_", $name);
        $ieme = intval($params[1]);
        $nbB = intval($params[2]);
        $lochashCl = $params[3];
        $maj = $params[4];
        $reqsel = "SELECT nom, taille, cluster, hex(hashT) AS HhashT FROM files WHERE rowid = " . $rowId;
        $pdo_result = $db->query($reqsel);
        $pdo_result->setFetchMode(PDO::FETCH_BOTH);
        $rows = $pdo_result->fetchAll();
        $pdo_result->closeCursor();
        $namef = $rows[0]['nom'];
        $sizef = bcadd("0", $rows[0]['taille']);
        $clusterf = 1048576 * $rows[0]['cluster'];
        $locHashT = $rows[0]['HhashT'];                 // écrit en hexa 20 caract par clef, 40 par paire
        //file_ecrit('receive_deb.txt','320: lochashT='.$locHashT."\n");  // ecrit en hexa
        $f2mod = fopen($namef, "rb"); // pas d'écriture
        for ($k = 0;$k < $ieme;$k++)
        {
            $nimp = fread($f2mod, $clusterf);
        }

        $path_parts = pathinfo($namef);
        $tmpfile = $path_parts['filename'] . "." . "bak";
        if ($path_parts['extension'] != "tmp")
        {
            $tmpfile = $path_parts['filename'] . "." . "tmp";
        }
        $data = fread($f2mod, $nbB);
        $ret = write_file($tmpfile, $data);
        $blocHash = sha1_file($tmpfile);
        echo $blocHash;
        fclose($f2mod);
        unlink($tmpfile);
        //hex2bin(substr($locHashT, 0, (80 * $ieme)));
        if ($maj == "O"){                                // mise à jour table en base si mise à jour
            $newhash = substr($locHashT, 0, (80 * $ieme)) . $lochashCl . $blocHash . substr($locHashT, 80 * ($ieme+1));
            $requpdate = "UPDATE files SET hashT = X'" . $newhash . "' WHERE rowid = " . $rowId;
            $db->beginTransaction();
            $stmt = $db->exec($requpdate);
            $db->commit();
        }
    }
    if (substr($name, 0, 6) == 'delete')     //rowId = recno to delete
    {
        $reqdel = "DELETE FROM files WHERE rowid = " . $rowId;
        $db->beginTransaction();
        $stmt = $db->prepare($reqdel);
        $ret = $stmt -> execute();
        $db->commit();
        echo $ret;
    }
}