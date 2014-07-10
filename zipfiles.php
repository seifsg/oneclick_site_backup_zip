<?php
//Kol chay fi ba3dhah 
//Il php il kol yab9a hna
// w html w css w javascript w jquery :D , bech ba3din kol chay fi fecha wa7da !
ignore_user_abort(false); 
set_time_limit(0);
define("DEMO",false);
ini_set('display_errors',true);

Class SZIP{
	public  $errors = array();
	protected function ZipStatusString( $status )
	{
		switch( (int) $status )
		{
			case ZipArchive::ER_OK           : return 'N No error';
			case ZipArchive::ER_MULTIDISK    : return 'N Multi-disk zip archives not supported';
			case ZipArchive::ER_RENAME       : return 'S Renaming temporary file failed';
			case ZipArchive::ER_CLOSE        : return 'S Closing zip archive failed';
			case ZipArchive::ER_SEEK         : return 'S Seek error';
			case ZipArchive::ER_READ         : return 'S Read error';
			case ZipArchive::ER_WRITE        : return 'S Write error';
			case ZipArchive::ER_CRC          : return 'N CRC error';
			case ZipArchive::ER_ZIPCLOSED    : return 'N Containing zip archive was closed';
			case ZipArchive::ER_NOENT        : return 'N No such file';
			case ZipArchive::ER_EXISTS       : return 'N File already exists';
			case ZipArchive::ER_OPEN         : return 'S Can\'t open file';
			case ZipArchive::ER_TMPOPEN      : return 'S Failure to create temporary file';
			case ZipArchive::ER_ZLIB         : return 'Z Zlib error';
			case ZipArchive::ER_MEMORY       : return 'N Malloc failure';
			case ZipArchive::ER_CHANGED      : return 'N Entry has been changed';
			case ZipArchive::ER_COMPNOTSUPP  : return 'N Compression method not supported';
			case ZipArchive::ER_EOF          : return 'N Premature EOF';
			case ZipArchive::ER_INVAL        : return 'N Invalid argument';
			case ZipArchive::ER_NOZIP        : return 'N Not a zip archive';
			case ZipArchive::ER_INTERNAL     : return 'N Internal error';
			case ZipArchive::ER_INCONS       : return 'N Zip archive inconsistent';
			case ZipArchive::ER_REMOVE       : return 'S Can\'t remove file';
			case ZipArchive::ER_DELETED      : return 'N Entry has been deleted';
		   
			default: return sprintf('Unknown status %s', $status );
		}
	}

	protected function FileSizeConvert($bytes)
	{
		$bytes = floatval($bytes);
			$arBytes = array(
				0 => array(
					"UNIT" => "TB",
					"VALUE" => pow(1024, 4)
				),
				1 => array(
					"UNIT" => "GB",
					"VALUE" => pow(1024, 3)
				),
				2 => array(
					"UNIT" => "MB",
					"VALUE" => pow(1024, 2)
				),
				3 => array(
					"UNIT" => "KB",
					"VALUE" => 1024
				),
				4 => array(
					"UNIT" => "B",
					"VALUE" => 1
				),
			);

		foreach($arBytes as $arItem)
		{
			if($bytes >= $arItem["VALUE"])
			{
				$result = $bytes / $arItem["VALUE"];
				$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
				break;
			}
		}
		return $result;
	}
	private $f_arr = array();
	protected function listdir($dir,$l = ''){
		chdir($dir);
		$dir = getcwd();
		if(is_dir($dir)){
			$ar = scandir($dir);unset($ar[0]);unset($ar[1]);
			foreach($ar as $a){
				$a = $dir."/".$a;
				if(!is_readable($a)) $this->errors['Directory is not readable'][] = $a;
				if(is_dir($a)){$this->listdir($a,$l.'--');	}
				$this->f_arr[] =  $a;
			}
		}
		
	}
	public function zipnow($dir,$tdir,$filename){
		if($dir == '') $dir = '.'; if($filename == '') $filename = 'bla001.zip';
		if($tdir == '') {$tdir = getcwd() ;}
		else{
			$tmp = getcwd() ; 
			if(!chdir($tdir)) 
			if(!mkdir($tdir,0755,true))
			$this->errors['Unable to create directory'][] = $tdir;
			chdir($tdir);
			$tdir = getcwd(); 
			$dir = $tmp;
		}
		chdir($dir);
		$r = getcwd();
		$this->listdir('.');
		$zip = new ZipArchive();
		$nwfilepath = $tdir.'/'.$filename;
		if(file_exists($nwfilepath))
		{
			$fileexistalready = true;
			$this->errors['File exist already'][] = $nwfilepath;
		}
		else
		{
			$fileexistalready = false;
		}
		$fileexistalready = false;
		if ((!$fileexistalready)&&($zip->open($nwfilepath, ZipArchive::CREATE)===TRUE)) {
			foreach($this->f_arr as $f){
				if(is_dir($f)) continue;
				$zip->addFile($f,str_replace($r,'',$f));
			}
			$msg = '';
			$msg .= "Number of zipped files: " . $zip->numFiles . "<br>";
			$cmsgzipstatus = substr($this->ZipStatusString($zip->status),1,strlen($this->ZipStatusString($zip->status))-1) ; // text ndhayef lil errors !
			//$msg .= "Errors(exit status):" . $zip->status .'	'.$cmsgzipstatus. "!<br>";
			if($zip->status > 0 )
			$this->errors['Exit status'][] = $cmsgzipstatus ;
			$zip->close();
			$msg .= " New file path : {$nwfilepath} <br>File Size : ".$this->FileSizeConvert(filesize($nwfilepath));
			if(!is_writable($tdir))
				$this->errors['Directory is not writable'][] = $tdir;
			else if(!file_exists($nwfilepath))
				$this->errors['Unable to create file'][] = $nwfilepath;
			else if (!chmod($nwfilepath,0777) )
				$this->errors['Unable to give permissions to the file'][] = $nwfilepath;
			
			return $msg;
			
		}

		
	}
	public function listerrors(){
		$errorcount = count($this->errors) ;
		if($errorcount>0){
			$youhaveerrors = true;
		}else{
			$youhaveerrors = false;
		}
		$errormsg = "";
		if($youhaveerrors){
			//listing errors
			$errormsg .= "<div id=\"errors\">";
			foreach($this->errors as $k=>$v){
				$errormsg .=  $k.": ";
				if(count($v)>1)
					$errormsg .=  "<br>";
				foreach($v as $kv){
					$errormsg .=  "	".$kv."<br>";
				}
			}
			$errormsg .= "Errors count:".$errorcount."<br>";
			$errormsg .= "</div>";
		}else{
			$errormsg .= "Done Successfully !";
		}
		return $errormsg;
	}
}

//bech ma tod5elch b3athha ken kif ycklicki 3al zipnow tet3adda commande !
//chway javascript lil control de saisie , html w css zina , jquery jaw :D
//w kol fi fecha wa7da bech teshel il 3amalya !
$b = isset($_POST['submit']);
if($b) {
	if(isset($_POST['dir'])) $dir = $_POST['dir'] ; else $dir='';
	if(isset($_POST['tdir'])) $tdir = $_POST['tdir'] ; else $tdir='';
	if(isset($_POST['name'])) $name = $_POST['name'] ; else $name='';
	$bma = new SZIP();
	
}else{

}  
 
?>

<!DOCTYPE html>
<html>
<head>
<title>Site migration , **Source**</title>
<?php // all the css goes here ! ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
<style>
.hidden { display: none; }

html { background-color: #F7F7F7; }

body
{
	background-color: #FFFFFF;
	margin: auto;
	padding: 20px 50px;
	width: 80%;
	box-shadow: 0 1px 8px rgba(0, 0, 0, 0.2);
}

content
{
	width: 100%;
	font-weight: 100;
	font-size: 20px;
	line-height: 50px;
}

form { }

content span
{
	float: left;
	display: block;
	width: 100%;
}

content input { float: right; }

content input[type="submit"]
{
	background: linear-gradient(#4DD2FF, #0DC2FF) repeat scroll 0 0 rgba(0, 0, 0, 0);
	border: medium none;
 //border-radius: 5px;
	box-shadow: 0 0 10px #000000;
	clear: both;
	color: #000000;
	float: right;
	font-size: 30px;
	font-weight: 100;
	height: 40px;
	margin-top: 30px;
	opacity: 0.5;
	text-align: center;
	text-shadow: 0 0 6px #FFFFFF;
	width: 200px;
}

content input[type="submit"]:hover
{
	opacity: 1;
	text-shadow: 1px 1px 6px #FFFFFF;
}

content input[type="text"]
{
	border: 1px solid #808080;
	height: 40px;
	padding-left: 10px;
	width: 50%;
	text-transform: none;
}

content input[type="text"]:focus { box-shadow: 0 0 4px #000000; }

header h1
{
	color: #000000;
	font-family: "Courier New", Courier, monospace;
	font-size: 4em;
	font-weight: 100;
	line-height: 70px;
	margin-top: 0;
	padding-bottom: 30px;
	text-align: center;
	text-shadow: 1.5px 1px 0 #000000;
	text-transform: uppercase;
}

.bla
{
	background: linear-gradient(#E6F9FF, #F2FCFF) repeat scroll 0 0 rgba(0, 0, 0, 0);
 //border-radius: 5px;
	box-shadow: 0 0 1px;
	display: inline-block;
	margin: 0 10px;
	padding: 10px;
	width: 90%;
}

.bla:hover {  //background: linear-gradient(#4DD2FF, #8CE2FF) repeat scroll 0 0 rgba(0, 0, 0, 0);
}

footer
{
	clear: both;
	font-family: "Lucida Console", Monaco, monospace;
	font-size: 14px;
	padding: 50px 10px;
	text-align: center;
}

*
{
	font-weight: 100;
	margin: 0;
	padding: 0;
	text-transform: capitalize;
	transition: all 0.2s ease 0s;
}

#msg
{
	text-transform: none;
	display: none;
	margin: 20px 0 90px;
}

#nmsg { margin: 100px 0; }

#errors
{
	background-color: rgba(255, 0, 50, 0.1);
	border-radius: 5px;
	box-shadow: 0 0 4px #808080;
	padding: 5px;
}
</style>
</head>
<body>
<header>
    <h1>Site Migration <br>
        ** Source ** </h1>
</header>
<content>
    <?php 
		if($b){
			echo "<div id='msg'>";
			if(!DEMO){ 
				echo $bma->zipnow($dir,$tdir,$name)."<br>"; 
				echo $bma->listerrors();
			}
			echo "</div>";
		}else{
	?>
    <div id='nmsg' style="display: none">Please be patient ! If your site is too big , this might take time !</div>
    <form method="post" action="<?php echo basename($_SERVER['SCRIPT_FILENAME']); ?>" name="zipform" id="zipform">
        <div class="bla"><span>Directory to zip : </span>
            <input type="text" value="" size="30" placeholder="keep empty for current directory" name="dir">
        </div>
        <div class="bla"><span>Zipped file targetted directory  : </span>
            <input type="text" size="30" value="" placeholder="keep empty for current directory" name="tdir">
        </div>
        <div class="bla"><span>Zip file name : </span>
            <input type="text" value="" size="30" placeholder="keep empty for bla001.zip as a name" name="name">
        </div>
        <input type="submit" name="submit" id="submit" value="Zip Now ! ">
    </form>
    <?php } ?>
</content>
<footer id="fbla" style="color: white;">Proudly Written By Seif Sgayer</footer>
<?php // javascript il kol hna !  ?>
<script src="//code.jquery.com/jquery-1.9.1.js"></script> 
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script> 
<script>
	$(document).ready(function(){
			$('#submit').click(function(){
					$('#zipform').toggle("fade", {duration: 1000,complete: function(){
							$('#nmsg').toggle('fade',1000);
					}
					});
			});		
			$('#nmsg').mouseenter(function(){$('#nmsg').css('color','green');	});
			$('#nmsg').mouseleave(function(){$('#nmsg').css('color','brown');	});
			$("#msg").toggle('slow');
			$('#fbla').mouseenter(function(){$(this).css('color','black');	});
			$('#fbla').mouseleave(function(){$(this).css('color','white');	});
	});


</script>
</body>
</html>
