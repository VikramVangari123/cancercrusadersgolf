<?php
require_once( dirname(__FILE__).'/form.lib.php' );

define( 'PHPFMG_USER', "anuraj@wowsomeapp.com" ); // must be a email address. for sending password to you.
define( 'PHPFMG_PW', "f25bf5" );

?>
<?php
/**
 * GNU Library or Lesser General Public License version 2.0 (LGPLv2)
*/

# main
# ------------------------------------------------------
error_reporting( E_ERROR ) ;
phpfmg_admin_main();
# ------------------------------------------------------




function phpfmg_admin_main(){
    $mod  = isset($_REQUEST['mod'])  ? $_REQUEST['mod']  : '';
    $func = isset($_REQUEST['func']) ? $_REQUEST['func'] : '';
    $function = "phpfmg_{$mod}_{$func}";
    if( !function_exists($function) ){
        phpfmg_admin_default();
        exit;
    };

    // no login required modules
    $public_modules   = false !== strpos('|captcha|', "|{$mod}|", "|ajax|");
    $public_functions = false !== strpos('|phpfmg_ajax_submit||phpfmg_mail_request_password||phpfmg_filman_download||phpfmg_image_processing||phpfmg_dd_lookup|', "|{$function}|") ;   
    if( $public_modules || $public_functions ) { 
        $function();
        exit;
    };
    
    return phpfmg_user_isLogin() ? $function() : phpfmg_admin_default();
}

function phpfmg_ajax_submit(){
    $phpfmg_send = phpfmg_sendmail( $GLOBALS['form_mail'] );
    $isHideForm  = isset($phpfmg_send['isHideForm']) ? $phpfmg_send['isHideForm'] : false;

    $response = array(
        'ok' => $isHideForm,
        'error_fields' => isset($phpfmg_send['error']) ? $phpfmg_send['error']['fields'] : '',
        'OneEntry' => isset($GLOBALS['OneEntry']) ? $GLOBALS['OneEntry'] : '',
    );
    
    @header("Content-Type:text/html; charset=$charset");
    echo "<html><body><script>
    var response = " . json_encode( $response ) . ";
    try{
        parent.fmgHandler.onResponse( response );
    }catch(E){};
    \n\n";
    echo "\n\n</script></body></html>";

}


function phpfmg_admin_default(){
    if( phpfmg_user_login() ){
        phpfmg_admin_panel();
    };
}



function phpfmg_admin_panel()
{    
    phpfmg_admin_header();
    phpfmg_writable_check();
?>    
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign=top style="padding-left:280px;">

<style type="text/css">
    .fmg_title{
        font-size: 16px;
        font-weight: bold;
        padding: 10px;
    }
    
    .fmg_sep{
        width:32px;
    }
    
    .fmg_text{
        line-height: 150%;
        vertical-align: top;
        padding-left:28px;
    }

</style>

<script type="text/javascript">
    function deleteAll(n){
        if( confirm("Are you sure you want to delete?" ) ){
            location.href = "admin.php?mod=log&func=delete&file=" + n ;
        };
        return false ;
    }
</script>


<div class="fmg_title">
    1. Email Traffics
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=1">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=1">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_EMAILS_LOGFILE) ){
            echo '<a href="#" onclick="return deleteAll(1);">delete all</a>';
        };
    ?>
</div>


<div class="fmg_title">
    2. Form Data
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=2">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=2">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_SAVE_FILE) ){
            echo '<a href="#" onclick="return deleteAll(2);">delete all</a>';
        };
    ?>
</div>

<div class="fmg_title">
    3. Form Generator
</div>
<div class="fmg_text">
    <a href="http://www.formmail-maker.com/generator.php" onclick="document.frmFormMail.submit(); return false;" title="<?php echo htmlspecialchars(PHPFMG_SUBJECT);?>">Edit Form</a> &nbsp;&nbsp;
    <a href="http://www.formmail-maker.com/generator.php" >New Form</a>
</div>
    <form name="frmFormMail" action='http://www.formmail-maker.com/generator.php' method='post' enctype='multipart/form-data'>
    <input type="hidden" name="uuid" value="<?php echo PHPFMG_ID; ?>">
    <input type="hidden" name="external_ini" value="<?php echo function_exists('phpfmg_formini') ?  phpfmg_formini() : ""; ?>">
    </form>

		</td>
	</tr>
</table>

<?php
    phpfmg_admin_footer();
}



function phpfmg_admin_header( $title = '' ){
    header( "Content-Type: text/html; charset=" . PHPFMG_CHARSET );
?>
<html>
<head>
    <title><?php echo '' == $title ? '' : $title . ' | ' ; ?></title>
    

    <style type='text/css'>
    body, td, label, div, span{
        font-family : Verdana, Arial, Helvetica, sans-serif;
        font-size : 12px;
    }
    </style>
</head>
<body  marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">

<table cellspacing=0 cellpadding=0 border=0 width="100%">
    <td nowrap align=center style="background-color:#024e7b;padding:10px;font-size:18px;color:#ffffff;font-weight:bold;width:250px;" >
        Form Admin Panel
    </td>
    <td style="padding-left:30px;background-color:#86BC1B;width:100%;font-weight:bold;" >
        &nbsp;
<?php
    if( phpfmg_user_isLogin() ){
        echo '<a href="admin.php" style="color:#ffffff;">Main Menu</a> &nbsp;&nbsp;' ;
        echo '<a href="admin.php?mod=user&func=logout" style="color:#ffffff;">Logout</a>' ;
    }; 
?>
    </td>
</table>

<div style="padding-top:28px;">

<?php
    
}


function phpfmg_admin_footer(){
?>

</div>

<div style="color:#cccccc;text-decoration:none;padding:18px;font-weight:bold;">
	
</div>

</body>
</html>
<?php
}


function phpfmg_image_processing(){
    $img = new phpfmgImage();
    $img->out_processing_gif();
}


# phpfmg module : captcha
# ------------------------------------------------------
function phpfmg_captcha_get(){
    $img = new phpfmgImage();
    $img->out();
    //$_SESSION[PHPFMG_ID.'fmgCaptchCode'] = $img->text ;
    $_SESSION[ phpfmg_captcha_name() ] = $img->text ;
}



function phpfmg_captcha_generate_images(){
    for( $i = 0; $i < 50; $i ++ ){
        $file = "$i.png";
        $img = new phpfmgImage();
        $img->out($file);
        $data = base64_encode( file_get_contents($file) );
        echo "'{$img->text}' => '{$data}',\n" ;
        unlink( $file );
    };
}


function phpfmg_dd_lookup(){
    $paraOk = ( isset($_REQUEST['n']) && isset($_REQUEST['lookup']) && isset($_REQUEST['field_name']) );
    if( !$paraOk )
        return;
        
    $base64 = phpfmg_dependent_dropdown_data();
    $data = @unserialize( base64_decode($base64) );
    if( !is_array($data) ){
        return ;
    };
    
    
    foreach( $data as $field ){
        if( $field['name'] == $_REQUEST['field_name'] ){
            $nColumn = intval($_REQUEST['n']);
            $lookup  = $_REQUEST['lookup']; // $lookup is an array
            $dd      = new DependantDropdown(); 
            echo $dd->lookupFieldColumn( $field, $nColumn, $lookup );
            return;
        };
    };
    
    return;
}


function phpfmg_filman_download(){
    if( !isset($_REQUEST['filelink']) )
        return ;
        
    $info =  @unserialize(base64_decode($_REQUEST['filelink']));
    if( !isset($info['recordID']) ){
        return ;
    };
    
    $file = PHPFMG_SAVE_ATTACHMENTS_DIR . $info['recordID'] . '-' . $info['filename'];
    phpfmg_util_download( $file, $info['filename'] );
}


class phpfmgDataManager
{
    var $dataFile = '';
    var $columns = '';
    var $records = '';
    
    function phpfmgDataManager(){
        $this->dataFile = PHPFMG_SAVE_FILE; 
    }
    
    function parseFile(){
        $fp = @fopen($this->dataFile, 'rb');
        if( !$fp ) return false;
        
        $i = 0 ;
        $phpExitLine = 1; // first line is php code
        $colsLine = 2 ; // second line is column headers
        $this->columns = array();
        $this->records = array();
        $sep = chr(0x09);
        while( !feof($fp) ) { 
            $line = fgets($fp);
            $line = trim($line);
            if( empty($line) ) continue;
            $line = $this->line2display($line);
            $i ++ ;
            switch( $i ){
                case $phpExitLine:
                    continue;
                    break;
                case $colsLine :
                    $this->columns = explode($sep,$line);
                    break;
                default:
                    $this->records[] = explode( $sep, phpfmg_data2record( $line, false ) );
            };
        }; 
        fclose ($fp);
    }
    
    function displayRecords(){
        $this->parseFile();
        echo "<table border=1 style='width=95%;border-collapse: collapse;border-color:#cccccc;' >";
        echo "<tr><td>&nbsp;</td><td><b>" . join( "</b></td><td>&nbsp;<b>", $this->columns ) . "</b></td></tr>\n";
        $i = 1;
        foreach( $this->records as $r ){
            echo "<tr><td align=right>{$i}&nbsp;</td><td>" . join( "</td><td>&nbsp;", $r ) . "</td></tr>\n";
            $i++;
        };
        echo "</table>\n";
    }
    
    function line2display( $line ){
        $line = str_replace( array('"' . chr(0x09) . '"', '""'),  array(chr(0x09),'"'),  $line );
        $line = substr( $line, 1, -1 ); // chop first " and last "
        return $line;
    }
    
}
# end of class



# ------------------------------------------------------
class phpfmgImage
{
    var $im = null;
    var $width = 73 ;
    var $height = 33 ;
    var $text = '' ; 
    var $line_distance = 8;
    var $text_len = 4 ;

    function phpfmgImage( $text = '', $len = 4 ){
        $this->text_len = $len ;
        $this->text = '' == $text ? $this->uniqid( $this->text_len ) : $text ;
        $this->text = strtoupper( substr( $this->text, 0, $this->text_len ) );
    }
    
    function create(){
        $this->im = imagecreate( $this->width, $this->height );
        $bgcolor   = imagecolorallocate($this->im, 255, 255, 255);
        $textcolor = imagecolorallocate($this->im, 0, 0, 0);
        $this->drawLines();
        imagestring($this->im, 5, 20, 9, $this->text, $textcolor);
    }
    
    function drawLines(){
        $linecolor = imagecolorallocate($this->im, 210, 210, 210);
    
        //vertical lines
        for($x = 0; $x < $this->width; $x += $this->line_distance) {
          imageline($this->im, $x, 0, $x, $this->height, $linecolor);
        };
    
        //horizontal lines
        for($y = 0; $y < $this->height; $y += $this->line_distance) {
          imageline($this->im, 0, $y, $this->width, $y, $linecolor);
        };
    }
    
    function out( $filename = '' ){
        if( function_exists('imageline') ){
            $this->create();
            if( '' == $filename ) header("Content-type: image/png");
            ( '' == $filename ) ? imagepng( $this->im ) : imagepng( $this->im, $filename );
            imagedestroy( $this->im ); 
        }else{
            $this->out_predefined_image(); 
        };
    }

    function uniqid( $len = 0 ){
        $md5 = md5( uniqid(rand()) );
        return $len > 0 ? substr($md5,0,$len) : $md5 ;
    }
    
    function out_predefined_image(){
        header("Content-type: image/png");
        $data = $this->getImage(); 
        echo base64_decode($data);
    }
    
    // Use predefined captcha random images if web server doens't have GD graphics library installed  
    function getImage(){
        $images = array(
			'309C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7RAMYAhhCGaYGIIkFTGEMYXR0CBBBVtnK2sraEOjAgiw2RaTRFSiG7L6VUdNWZmZGZqG4D6jOIQSuDmoeUKwBXYy1lRHNDmxuwebmgQo/KkIs7gMA+vzKburlqM4AAAAASUVORK5CYII=',
			'EAD1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkMYAlhDGVqRxQIaGENYGx2mooqxtrI2BISiiok0ujYEwPSCnRQaNW1l6qqopcjuQ1MHFRMNxRTDpg4o1uiAIhYaAhQLZQgNGAThR0WIxX0ATljPC/Te1m8AAAAASUVORK5CYII=',
			'6011' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WAMYAhimMLQii4lMYQxhCGGYiiwW0MLaChQNRRFrEGl0QOgFOykyatrKrGmrliK7L2QKijqI3lZsYqytWN2CJgZyM2OoQ2jAIAg/KkIs7gMAUDHLp5p9wqQAAAAASUVORK5CYII=',
			'BAC4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7QgMYAhhCHRoCkMQCpjCGMDoENKKItbK2sjYItKKqE2l0bWCYEoDkvtCoaStTV62KikJyH0Qd0EQU80RDgWKhIShiIHUCDeh2OAJ1IouFBog0OqC5eaDCj4oQi/sAooXQC4KGDeQAAAAASUVORK5CYII=',
			'D084' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QgMYAhhCGRoCkMQCpjCGMDo6NKKItbK2sgJJVDGRRkdHhykBSO6LWjptZVboqqgoJPdB1Dk6oOt1bQgMDcG0A5tbUMSwuXmgwo+KEIv7AJW8zvTxcF/6AAAAAElFTkSuQmCC',
			'8AAD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7WAMYAhimMIY6IImJTGEMYQhldAhAEgtoZW1ldHR0EEFRJ9Lo2hAIEwM7aWnUtJWpqyKzpiG5D00d1DzRUNdQdDFMdTC9yG5hDQCLobh5oMKPihCL+wB1G8zxjA1nIAAAAABJRU5ErkJggg==',
			'AEA5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB1EQxmmMIYGIImxBog0MIQyOiCrE5ki0sDo6IgiFtAq0sDaEOjqgOS+qKVTw5auioyKQnIfRF1AgwiS3tBQoFgoqhjUPAdMsYCAABQx0VCg2FSHQRB+VIRY3AcAkPnMYMLn8P4AAAAASUVORK5CYII=',
			'FF0B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7QkNFQx2mMIY6IIkFNIg0MIQyOgSgiTE6OjqIoImxNgTC1IGdFBo1NWzpqsjQLCT3oalDEUM3D5sd2NzCgObmgQo/KkIs7gMAkY7MhHYP6IcAAAAASUVORK5CYII=',
			'35DC' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7RANEQ1lDGaYGIIkFTBFpYG10CBBBVtkKFGsIdGBBFpsiEgISQ3bfyqipS5euisxCcd8UhkZXhDqoedjERMBiyHYETGFtRXeLaABjCLqbByr8qAixuA8A7IvMBVkLKE4AAAAASUVORK5CYII=',
			'2BC5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7WANEQxhCHUMDkMREpoi0MjoEOiCrC2gVaXRtEEQRY2gVaWVtYHR1QHbftKlhS1etjIpCdl8ASB3QXCS9jA4g81DFWBsgdiCLiTSA3BIQgOy+0FCQmx2mOgyC8KMixOI+AKysyyfNLHkyAAAAAElFTkSuQmCC',
			'D215' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nM2QsRHAIAhFocgGZh8s0lNI4zRauAHJBhZxyljimTK5k9+9+3DvgDZNgpXyi58wBlAUNox1KxCQbI+Ly35ikEnxIOMXa6vtumM0fr2nPcmNuzyzfl+R3OiSeo+tn/AuXuikBf73YV78HmxqzJFPbKobAAAAAElFTkSuQmCC',
			'4B33' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpI37poiGMIYyhDogi4WItLI2OjoEIIkxhog0OjQENIggibFOEWllAIsi3Ddt2tSwVVNXLc1Ccl8AqjowDA3FNI9hClYxDLdgdfNAhR/1IBb3AQBFrc4BW/tB9AAAAABJRU5ErkJggg==',
			'CA16' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WEMYAhimMEx1QBITaWUMYQCKByCJBTSyAkUZHQSQxRpEGh2mMDoguy9q1bSVWdNWpmYhuQ+qDtW8BtFQkF4RFDsg5omguAUkhuoW1hCRRsdQBxQ3D1T4URFicR8ADAHMfEmG9tIAAAAASUVORK5CYII=',
			'1FB9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7GB1EQ11DGaY6IImxOog0sDY6BAQgiYmCxBoCgSSyXpA6R5gY2Ekrs6aGLQ1dFRWG5D6IOoepGHobAhqwiGGxA80tIUAxNDcPVPhREWJxHwAu/8ngntEHjgAAAABJRU5ErkJggg==',
			'0B94' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7GB1EQxhCGRoCkMRYA0RaGR0dGpHFRKaINLo2BLQiiwW0irSyNgRMCUByX9TSqWErM6OiopDcB1LHEBLogKa30aEhMDQEzQ5HoEuwuAVFDJubByr8qAixuA8A773Np45cPsgAAAAASUVORK5CYII=',
			'2290' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WAMYQxhCGVqRxUSmsLYyOjpMdUASC2gVaXRtCAgIQNbdygAUC3QQQXbftFVLV2ZGZk1Ddl8AwxSGELg6MGR0AIo2oIqxAkUZ0ewQAYmiuSU0VDTUAc3NAxV+VIRY3AcAHHvLSc2sBA4AAAAASUVORK5CYII=',
			'4F79' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpI37poiGuoYGTHVAFgsRAZIBAQFIYoxgsUAHESQx1ilAXqMjTAzspGnTpoatWroqKgzJfQEgdVMYpiLrDQ0F8gIYGkRQ3CLSwOjA4IAuxgpUGYAphurmgQo/6kEs7gMA34zL1KYqMWEAAAAASUVORK5CYII=',
			'78E1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkMZQ1hDHVpRRFtZW1kbGKaiiok0ujYwhKKITQGrg+mFuClqZdjS0FVLkd3H6ICiDgxZG8DmoYiJYBELaMDUG9AAdnNowCAIPypCLO4DAOlwy1gjtV22AAAAAElFTkSuQmCC',
			'860D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7WAMYQximMIY6IImJTGFtZQhldAhAEgtoFWlkdHR0EEFRJ9LA2hAIEwM7aWnUtLClqyKzpiG5T2SKaCuSOrh5rljEHDHswHQLNjcPVPhREWJxHwC8Zssel6JHswAAAABJRU5ErkJggg==',
			'EFAF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAV0lEQVR4nGNYhQEaGAYTpIn7QkNEQx2mMIaGIIkFNIg0MIQyOjCgiTE6OmKIsTYEwsTATgqNmhq2dFVkaBaS+9DUIcRCsYhhU4cmFhqCKTZQ4UdFiMV9AES4y4g/qaUmAAAAAElFTkSuQmCC',
			'DE4C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7QgNEQxkaHaYGIIkFTBFpYGh1CBBBFmsF8qY6OrCgiwU6OiC7L2rp1LCVmZlZyO4DqWNthKtDiIUGYogxNKLZAXJLI6pbsLl5oMKPihCL+wCqSM1dA0NpuQAAAABJRU5ErkJggg==',
			'6AB8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGaY6IImJTGEMYW10CAhAEgtoYW1lbQh0EEEWaxBpdEWoAzspMmraytTQVVOzkNwXMgVFHURvq2ioK7p5rUB1aGIiWPSyBgDF0Nw8UOFHRYjFfQBkg85GQdgYuAAAAABJRU5ErkJggg==',
			'571F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QkNEQx2mMIaGIIkFNDA0OoQwOjCgiTmiiQUGMLQyTIGLgZ0UNm0VEK4MzUJ2XytDAJI6qBiQjyYW0MragC4mMkUEQ4w1QKSBMdQR1bwBCj8qQizuAwDmJck9+Bl51AAAAABJRU5ErkJggg==',
			'BF01' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7QgNEQx2mMLQiiwVMEWlgCGWYiiLWKtLA6OgQiq6OFSiD7L7QqKlhS1dFLUV2H5o6uHnYxIB2YHMLilhoAFBsCkNowCAIPypCLO4DAMvWzYJb3iPCAAAAAElFTkSuQmCC',
			'DA86' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QgMYAhhCGaY6IIkFTGEMYXR0CAhAFmtlbWVtCHQQQBETaXR0dHRAdl/U0mkrs0JXpmYhuQ+qDs080VBXoHkiaOZhiE0B6UV1S2iASKMDmpsHKvyoCLG4DwD43s31TXppAAAAAABJRU5ErkJggg==',
			'225C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdklEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDHaYGIImJTGFtZW1gCBBBEgtoFWl0bWB0YEHW3crQ6DqV0QHFfdNWLV2amZmF4r4AhikMDYEOyPYCdQWgi7ECRVmBYsh2iIBEHR1Q3BIaKhrqEMqA4uaBCj8qQizuAwDZycpCwdxYDwAAAABJRU5ErkJggg==',
			'1909' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7GB0YQximMEx1QBJjdWBtZQhlCAhAEhN1EGl0dHR0EEHRK9Lo2hAIEwM7aWXW0qWpq6KiwpDcB7Qj0LUhYCqqXgag3oAGVDEWoB0OaHZgcUsIppsHKvyoCLG4DwBtIslE4T5txgAAAABJRU5ErkJggg==',
			'650C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WANEQxmmMEwNQBITmSLSwBDKECCCJBbQItLA6OjowIIs1iASwtoQ6IDsvsioqUuXrorMQnZfyBSGRleEOojeVmxiIo2OaHaITGFtRXcLawBjCLqbByr8qAixuA8AS2LLiFwcBJAAAAAASUVORK5CYII=',
			'798C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMZQxhCGaYGIIu2srYyOjoEiKCIiTS6NgQ6sCCLTRFpdHR0dEBxX9TSpVmhK7OQ3cfowBiIpA4MWRsYwOYhi4k0sGDYEdCA6ZaABixuHqDwoyLE4j4AclHK5+9+YXYAAAAASUVORK5CYII=',
			'B7DB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QgNEQ11DGUMdkMQCpjA0ujY6OgQgi7UCxRoCHURQ1bWyAsUCkNwXGrVq2tJVkaFZSO4DqgtAUgc1j9GBFd08oGkYYlNEGljR3BIaABRDc/NAhR8VIRb3AQBQUc3BzIpZUgAAAABJRU5ErkJggg==',
			'5321' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7QkNYQxhCGVqRxQIaRFoZHR2moooxNLo2BIQiiwUGgPQFwPSCnRQ2bVXYqpVZS1Hc1wqFyDa3MjQ6TEGzFyQWgComMgXoFgdUMdYA1hDW0IDQgEEQflSEWNwHAMMJy7cUnkXIAAAAAElFTkSuQmCC',
			'0C8F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7GB0YQxlCGUNDkMRYA1gbHR0dHZDViUwRaXBtCEQRC2gVaWBEqAM7KWrptFWrQleGZiG5D00dXIwVzTxsdmBzC9TNKGIDFX5UhFjcBwDXDMlrbgTRhgAAAABJRU5ErkJggg==',
			'0B5C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7GB1EQ1hDHaYGIImxBoi0sjYwBIggiYlMEWl0BapmQRILaAWqm8rogOy+qKVTw5ZmZmYhuw+kjqEh0IEBVW+jA5oYxI5AFDtAbmF0dEBxC8jNDKEMKG4eqPCjIsTiPgB4B8rZJ23ESgAAAABJRU5ErkJggg==',
			'E7A3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7QkNEQx2mMIQ6IIkFNDA0OoQyOgSgiTk6OjSIoIq1sgLJACT3hUatmrZ0VdTSLCT3AeUDkNRBxRgdWEMD0MxjbQCpQxUTAYoForglNAQkFoDi5oEKPypCLO4DAE94zskcm8o0AAAAAElFTkSuQmCC',
			'0CB5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7GB0YQ1lDGUMDkMRYA1gbXRsdHZDViUwRaXBtCEQRC2gVaWBtdHR1QHJf1NJpq5aGroyKQnIfRJ1Dgwi63oYAFDGYHSIYbnEIQHYfxM0MUx0GQfhREWJxHwAyH8xjGwi+iAAAAABJRU5ErkJggg==',
			'71F1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QkMZAlhDA1pRRFsZA1gbGKaiirGCxEJRxKYwgMRgeiFuiloVtTR01VJk9zE6oKgDQyAfQ0wEi1gAVjHWUJBbAgZB+FERYnEfAPBEyOc43DVBAAAAAElFTkSuQmCC',
			'4225' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeElEQVR4nM2QsQ2EQAwE18F1AP2Y4HIjnRM6oAsT0IGfDj7gqnw+8wlCkNjNRrY0WtRTDG/qM35OBUoqkZW00jBwvKPSLdnGhiXHwjZmDn7bVr91n6cp+InDscK68KsKOWjDDheGELcs2Z82ft5rVvnwG/a7rxd+P/UKyo8WOT6hAAAAAElFTkSuQmCC',
			'99E5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDHUMDkMREprC2sjYwOiCrC2gVaXTFLubqgOS+aVOXLk0NXRkVheQ+VlfGQFeQucg2tzI0oosJtLKA7UAWg7iFIQDZfRA3O0x1GAThR0WIxX0AaZfK1prqNQ8AAAAASUVORK5CYII=',
			'8AD0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGVqRxUSmMIawNjpMdUASC2hlbWVtCAgIQFEn0ujaEOggguS+pVHTVqauisyahuQ+NHVQ80RDMcVA6rDYgeYW1gCgGJqbByr8qAixuA8AKCDOGFRl43IAAAAASUVORK5CYII=',
			'1AAE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7GB0YAhimMIYGIImxOjCGMIQCZZDERB1YWxkdHR1Q9Yo0ujYEwsTATlqZNW1l6qrI0Cwk96Gpg4qJhrqGoothU4cpJhoCFkNx80CFHxUhFvcBAAi8yKD3ZL3IAAAAAElFTkSuQmCC',
			'6AB1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGVqRxUSmMIawNjpMRRYLaGFtZW0ICEURaxBpdG10gOkFOykyatrK1NBVS5HdFzIFRR1Eb6toqCuQRBUDqkMTE8GilzUAKBbKEBowCMKPihCL+wAalM4aTcuFzAAAAABJRU5ErkJggg==',
			'DD54' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QgNEQ1hDHRoCkMQCpoi0sjYwNKKItYo0ujYwtGKITWWYEoDkvqil01amZmZFRSG5D6TOoSHQAV0vUCw0BMOOAAy3MDqiug/kZoZQBhSxgQo/KkIs7gMAdZbQbFgB2K4AAAAASUVORK5CYII=',
			'E941' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkMYQxgaHVqRxQIaWFsZWh2mooqJNDpMdQjFEAuE6wU7KTRq6dLMzKylyO4LaGAMdMWwg6HRNTQATYyl0QGbW9DEoG4ODRgE4UdFiMV9ACi6zp1S6TR4AAAAAElFTkSuQmCC',
			'482E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpI37pjCGMIQyhgYgi4WwtjI6Ojogq2MMEWl0bQhEEWOdwtrKgBADO2natJVhq1ZmhmYhuS8ApK6VEUVvaKhIo8MUVDGGKUCxAHQxoFsc0MUYQ1hDA1HdPFDhRz2IxX0AeJXJRe0xLwcAAAAASUVORK5CYII=',
			'90E1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7WAMYAlhDHVqRxUSmMIawNjBMRRYLaGVtBYqFooqJNLo2MMD0gp00beq0lamhq5Yiu4/VFUUdBLZiiglA7MDmFhQxqJtDAwZB+FERYnEfAH/cytFY4Yp3AAAAAElFTkSuQmCC',
			'ADBD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7GB1EQ1hDGUMdkMRYA0RaWRsdHQKQxESmiDS6NgQ6iCCJBbQCxYDqRJDcF7V02srU0JVZ05Dch6YODENDcZiHKYbhloBWTDcPVPhREWJxHwBYds1sDOCSOgAAAABJRU5ErkJggg==',
			'2585' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdklEQVR4nM2QPQ6AMAhGYegN8D506I5JWXoaHHoD9QYO9pR2pNFRE/m2F35egHYrgz/lE78gk4KiimO0kmGM7PukkgWbBwaVcu9L7P327Wh6luL9BJa+zsjNIsOSTAYWjDqb2bN+tWJk8X6qmEFh4x/878U8+F0p7MrNTfgFZgAAAABJRU5ErkJggg==',
			'8BC8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXklEQVR4nGNYhQEaGAYTpIn7WANEQxhCHaY6IImJTBFpZXQICAhAEgtoFWl0bRB0EEFTx9rAAFMHdtLSqKlhS1etmpqF5D40dUjmMaKYh8sOdLdgc/NAhR8VIRb3AQD8BszZwkuMDQAAAABJRU5ErkJggg==',
			'1C65' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7GB0YQxlCGUMDkMRYHVgbHR0dHZDViTqINLg2oIoxAsVYGxhdHZDctzJr2qqlU1dGRSG5D6zO0aFBBENvAIaYa0OgA6oYyC0OAcjuEw0BuZlhqsMgCD8qQizuAwARzMkiXN/FjwAAAABJRU5ErkJggg==',
			'9A81' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WAMYAhhCGVqRxUSmMIYwOjpMRRYLaGVtZW0ICEUVE2l0dHSA6QU7adrUaSuzQlctRXYfqyuKOghsFQ11BZqALCYANA9dTGQKpl7WAJFGh1CG0IBBEH5UhFjcBwD+28xVHo15cAAAAABJRU5ErkJggg==',
			'F2FA' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMZQ1hDA1qRxQIaWFtZGximOqCIiTS6NjAEBKCIMQDFGB1EkNwXGrVq6dLQlVnTkNwHVDeFFaEOJhYAFAsNQRFjdMBUx9qAKSYa6oomNlDhR0WIxX0AOsrL/j5jzr0AAAAASUVORK5CYII=',
			'FC06' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAW0lEQVR4nGNYhQEaGAYTpIn7QkMZQxmmMEx1QBILaGBtdAhlCAhAERNpcHR0dBBAE2NtCHRAdl9o1LRVS1dFpmYhuQ+qDsM8kF4RLHaIEHQLppsHKvyoCLG4DwDUhs2TgCerSwAAAABJRU5ErkJggg==',
			'5CFB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QkMYQ1lDA0MdkMQCGlgbXRsYHQJQxEQaQGIiSGKBASINrAh1YCeFTZu2amnoytAsZPe1oqhDEUM2L6AV0w6RKZhuYQ0AurmBEcXNAxV+VIRY3AcAjPTLfE409xEAAAAASUVORK5CYII=',
			'BD7C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QgNEQ1hDA6YGIIkFTBFpBZIBIshirSKNDg2BDiyo6hodGh0dkN0XGjVtZdbSlVnI7gOrm8LowIBuXgCmmKMDI7odrawNDChuAbu5gQHFzQMVflSEWNwHAFBrzaQ9qZjnAAAAAElFTkSuQmCC',
			'1314' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB1YQximMDQEIImxOoi0MoQwNCKLiTowNDqGMLQGoOhlaAXqnRKA5L6VWavCVk1bFRWF5D6IOkYHNL2NDlMYQ0MwxFDdwghyC5qYaAhrCGOoA4rYQIUfFSEW9wEAy03KcpMOJlAAAAAASUVORK5CYII=',
			'43B8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpI37prCGsIYyTHVAFgsRaWVtdAgIQBJjDGFodG0IdBBBEmOdwoCsDuykadNWhS0NXTU1C8l9AajqwDA0FNM8hinYxDDdgtXNAxV+1INY3AcA3dfM31ZjGPIAAAAASUVORK5CYII=',
			'5892' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGaY6IIkFNLC2Mjo6BASgiIk0ujYEOoggiQUGsLaygmSQ3Bc2bWXYysyoVVHI7mtlbWUICWhEtoOhVQTID2hFdksAUMyxIWAKspjIFIhbkMVYA0BuZgwNGQThR0WIxX0A1njMjLjlyNAAAAAASUVORK5CYII=',
			'0FB6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7GB1EQ11DGaY6IImxBog0sDY6BAQgiYlMAYo1BDoIIIkFtILUOToguy9q6dSwpaErU7OQ3AdVh2IeWAxonggWO0QIuIURqIIVzc0DFX5UhFjcBwDk7cv++pegGwAAAABJRU5ErkJggg==',
			'16B2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDGaY6IImxOrC2sjY6BAQgiYk6iDSyNgQ6iKDoFWkAqmsQQXLfyqxpYUtDV62KQnIfo4MoyLxGB1S9ja4NAa0MmGJTUMUgbkEWEw0BuZkxNGQQhB8VIRb3AQCJHcojctZGjwAAAABJRU5ErkJggg==',
			'6CBB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7WAMYQ1lDGUMdkMREprA2ujY6OgQgiQW0iDS4NgQ6iCCLNYg0sCLUgZ0UGTVt1dLQlaFZSO4LmYKiDqK3FSiGbl4rph3Y3ILNzQMVflSEWNwHAFwyzSaKi5aTAAAAAElFTkSuQmCC',
			'9AE5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WAMYAlhDHUMDkMREpjCGsDYwOiCrC2hlbcUUE2l0bWB0dUBy37Sp01amhq6MikJyH6srSB3QXGSbW0VD0cUEIOY5IIuJTAHrDUB2H2sAUCzUYarDIAg/KkIs7gMAmuPLTRUJYX0AAAAASUVORK5CYII=',
			'1617' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nM2QwQ3AIAhF+Qc30H3oBh6wQ3QKPbiBcYc6Ze0Na49tWjgQXiC8QG2KSH/KV/zAECoIophhk0koWsUc24QLA/euUPTKb9/q2movyg/scp/L412buJzbE/Mj6y4FrJkTCMIysK/+92De+B1RYsg/R5u6NgAAAABJRU5ErkJggg==',
			'D5E2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7QgNEQ1lDHaY6IIkFTBFpYG1gCAhAFmsFiTE6iKCKhQDVNYgguS9q6dSlS0OBNJL7AloZGl0bGBpR7ICItTKgmgcSm4IiNoW1FeQWVDczhrCGOoaGDILwoyLE4j4ACDbNoEzwy9MAAAAASUVORK5CYII=',
			'B274' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7QgMYQ1hDAxoCkMQCprC2AslGFLFWkUYHIImqjqHRodFhSgCS+0KjVi0FwqgoJPcB1QEhowOqeQwBDAGMoSEoYowOjA4M6G5pYG1AFQsNEA11RRMbqPCjIsTiPgB5f89XGb5SZgAAAABJRU5ErkJggg==',
			'C7D8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WENEQ11DGaY6IImJtDI0ujY6BAQgiQU0AsUaAh1EkMUaGFpZGwJg6sBOilq1atrSVVFTs5DcB5QPQFIHFWN0YEU3r5G1AV1MpFWkgRXNLawhQDE0Nw9U+FERYnEfADdsza6MB4/NAAAAAElFTkSuQmCC',
			'3383' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAW0lEQVR4nGNYhQEaGAYTpIn7RANYQxhCGUIdkMQCpoi0Mjo6OgQgq2xlaHRtCGgQQRabwgBU59AQgOS+lVGrwlaFrlqahew+VHW4zcMihs0t2Nw8UOFHRYjFfQBQz8xZXe2QmwAAAABJRU5ErkJggg==',
			'B431' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QgMYWhlDGVqRxQKmMExlbXSYiiLWyhAKJENR1TG6MjQ6wPSCnRQatXTpqqmrliK7L2CKSCuSOqh5oqEOIFNR7WhlQBebwtDKiqYX6ubQgEEQflSEWNwHAJL3zjNPS6CVAAAAAElFTkSuQmCC',
			'561E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMYQximMIYGIIkFNLC2MoQwOjCgiIk0MqKJBQaINAD1wsTATgqbNi1s1bSVoVnI7msVbUVSBxUTaXRAEwvAIiYyhRVDL2sA0CWhjihuHqjwoyLE4j4A2UrJhhICi6YAAAAASUVORK5CYII=',
			'A650' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeklEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDHVqRxVgDWFtZGximOiCJiUwRaQSKBQQgiQW0ijSwTmV0EEFyX9TSaWFLMzOzpiG5L6BVFGh+IEwdGIaGijQ6oIkBzWt0bQhAs4O1ldHRAcUtAa2MIQyhDChuHqjwoyLE4j4A/v7MZqDhSB4AAAAASUVORK5CYII=',
			'C044' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7WEMYAhgaHRoCkMREWhlDGFodGpHFAhpZWxmmOrSiiDWINDoEOkwJQHJf1KppKzMzs6KikNwHUufa6OiArtc1NDA0BN0ObG5BE8Pm5oEKPypCLO4DAJh/zvWhuKslAAAAAElFTkSuQmCC',
			'33AD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7RANYQximMIY6IIkFTBFpZQhldAhAVtnK0Ojo6Ogggiw2haGVtSEQJgZ20sqoVWFLV0VmTUN2H6o6uHmuoVjE0NSB3ALSi+wWkJuBYihuHqjwoyLE4j4AMwvLpdGsd0EAAAAASUVORK5CYII=',
			'EA1B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkMYAhimMIY6IIkFNDCGMIQwOgSgiLG2MgLFRFDERBodpsDVgZ0UGjVtZda0laFZSO5DUwcVEw0FiWEzD48dUDeLNDqGOqK4eaDCj4oQi/sAebfMy+IMeF8AAAAASUVORK5CYII=',
			'236D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WANYQxhCGUMdkMREpoi0Mjo6OgQgiQW0MjS6Njg6iCDrbmVoZW1ghIlB3DRtVdjSqSuzpiG7LwCozhFVL6MDyLxAFDHWBkwxkQZMt4SGYrp5oMKPihCL+wDoQMpp1CFvNQAAAABJRU5ErkJggg==',
			'C385' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WENYQxhCGUMDkMREWkVaGR0dHZDVBTQyNLo2BKKKNTCA1Lk6ILkvatWqsFWhK6OikNwHUefQIIKqF2heAKoY1A4RDLc4BCC7D+JmhqkOgyD8qAixuA8AOG3Lo5Cltd0AAAAASUVORK5CYII=',
			'D019' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7QgMYAhimMEx1QBILmMIYwhDCEBCALNbK2soYwugggiIm0ugwBS4GdlLU0mkrs6atigpDch9EHcNUTL0MDSJodgDdgmoHyC1TUN0CcjNjqAOKmwcq/KgIsbgPANbhzNf80nfbAAAAAElFTkSuQmCC',
			'4B38' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpI37poiGMIYyTHVAFgsRaWVtdAgIQBJjDBFpdGgIdBBBEmOdItLKgFAHdtK0aVPDVk1dNTULyX0BqOrAMDQU0zyGKVjFMNyC1c0DFX7Ug1jcBwCXjs1yA4pb4QAAAABJRU5ErkJggg==',
			'3EC3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7RANEQxlCHUIdkMQCpog0MDoEOgQgq2wVaWBtEGgQQRabAhIDqkdy38qoqWFLV61amoXsPlR1SOYxoJqHxQ5sbsHm5oEKPypCLO4DABIDzBcdKgSHAAAAAElFTkSuQmCC',
			'36F0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7RAMYQ1hDA1qRxQKmsLayNjBMdUBW2SrSCBQLCEAWmyLSwNrA6CCC5L6VUdPCloauzJqG7L4poq1I6uDmuWIVQ7UDm1vAbgaqHgzhR0WIxX0AxwDLFrBYxGQAAAAASUVORK5CYII=',
			'DE61' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7QgNEQxlCGVqRxQKmiDQwOjpMRRFrFWlgbXAIxRSD6wU7KWrp1LClU1ctRXYfWJ2jQyum3gDCYhC3oIhB3RwaMAjCj4oQi/sAimDNWEpCRyIAAAAASUVORK5CYII=',
			'7273' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7QkMZQ1hDA0IdkEVbWVsZGgIdAlDERBodGgIaRJDFpjA0OoBFkdwXtWopCGYhuY/RAahyCkMDsnmsDQwBQIhinghQJVAtilgAUCUrUDwARUw01LWBAdXNAxR+VIRY3AcA5k3Mk+5H+30AAAAASUVORK5CYII=',
			'4BBB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpI37poiGsIYyhjogi4WItLI2OjoEIIkxhog0ujYEOoggibFOQVEHdtK0aVPDloauDM1Ccl/AFEzzQkMxzWOYglUMQy9WNw9U+FEPYnEfAC1bzFOX5a+hAAAAAElFTkSuQmCC',
			'30E4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7RAMYAlhDHRoCkMQCpjCGsDYwNCKLMbSytgLFWlHEpog0ugLJACT3rYyatjI1dFVUFLL7wOoYHVDNA4uFhmDagc0tKGLY3DxQ4UdFiMV9AL2GzKdDNhgOAAAAAElFTkSuQmCC',
			'640C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7WAMYWhmmMEwNQBITAfIZQhkCRJDEAloYQhkdHR1YkMUaGF1ZGwIdkN0XGbV06dJVkVnI7guZItKKpA6it1U01BVDjKEV3Q6gW1rR3YLNzQMVflSEWNwHAIpDyu8nfocxAAAAAElFTkSuQmCC',
			'1FAA' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7GB1EQx2mMLQii7E6iDQwhDJMdUASEwWKMTo6BASg6BVpYG0IBKmGu29l1tSwpasis6YhuQ9NHUIsNDA0BLd5OMVEQzDFBir8qAixuA8AfdrJT/Sa8KUAAAAASUVORK5CYII=',
			'705F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QkMZAlhDHUNDkEVbGUNYGxgdUFS2srZiiE0RaXSdCheDuClq2srUzMzQLCT3MTqINDo0BKLoZW3AFBNpANmBKhbQwBjC6OiIJsYQwBCK5pYBCj8qQizuAwAO3cjfYx3iUQAAAABJRU5ErkJggg==',
			'398F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7RAMYQxhCGUNDkMQCprC2Mjo6OqCobBVpdG0IRBWbItLoiFAHdtLKqKVLs0JXhmYhu28KY6AjhnkMmOa1smCIYXML1M2oegco/KgIsbgPAN9hyWssR45mAAAAAElFTkSuQmCC',
			'3B91' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7RANEQxhCGVqRxQKmiLQyOjpMRVHZKtLo2hAQiiIGVMfaEADTC3bSyqipYSszo5aiuA+ojiEkoBXdPIcGTDFHNDGoW1DEoG4ODRgE4UdFiMV9ACTwzDw8E/aXAAAAAElFTkSuQmCC',
			'005B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7GB0YAlhDHUMdkMRYAxhDWIEyAUhiIlNYW0FiIkhiAa0ija5T4erATopaOm1lamZmaBaS+0DqHBoCUcyDiYlg2IEqBnILo6Mjil6QmxlCGVHcPFDhR0WIxX0A0DzKUOb++dsAAAAASUVORK5CYII=',
			'5EA8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkNEQxmmMEx1QBILaBBpYAhlCAhAE2N0dHQQQRILDBBpYG0IgKkDOyls2tSwpauipmYhu68VRR1CLDQQxbwAsDpUMZEpmHpZA0RDgWIobh6o8KMixOI+AORwzNcJ2cyUAAAAAElFTkSuQmCC',
			'23AE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WANYQximMIYGIImJTBFpZQhldEBWF9DK0Ojo6IgixtDK0MraEAgTg7hp2qqwpasiQ7OQ3ReAog4MgaY3uoaiirE2AMXQ1Ik0iGDoDQ1lDQGKobh5oMKPihCL+wBPjMofMBX4aAAAAABJRU5ErkJggg==',
			'254D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpIn7WANEQxkaHUMdkMREpog0MLQ6OgQgiQW0AsWmOjqIIOtuFQlhCISLQdw0berSlZmZWdOQ3RfA0OjaiKqX0QEoFhqIIsbaINLogKZOpIG1Feg+FLeEhjKGoLt5oMKPihCL+wCDI8uxWFiNNQAAAABJRU5ErkJggg==',
			'8BA8' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7WANEQximMEx1QBITmSLSyhDKEBCAJBbQKtLo6OjoIIKmjrUhAKYO7KSlUVPDlq6KmpqF5D40dXDzXEMDUcwDizUE4rMD7magGIqbByr8qAixuA8A0prNwoKVBoIAAAAASUVORK5CYII=',
			'E51C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7QkNEQxmmMEwNQBILaBBpYAhhCBBBE2MMYXRgQRULYZjC6IDsvtCoqUtXTVuZhew+oNmNDgh1eMREwGKodrC2At2H4pbQEKBLQh1Q3DxQ4UdFiMV9AMVxy/lK3McyAAAAAElFTkSuQmCC',
			'D3CC' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QgNYQxhCHaYGIIkFTBFpZXQICBBBFmtlaHRtEHRgQRVrZW1gdEB2X9TSVWFLV63MQnYfmjok87CJodmBxS3Y3DxQ4UdFiMV9AMZkzKaEikpDAAAAAElFTkSuQmCC',
			'B7A3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7QgNEQx2mMIQ6IIkFTGFodAhldAhAFmtlaHR0dGgQQVXXytoQ0BCA5L7QqFXTlq6KWpqF5D6gugAkdVDzGB1YQwNQzQOaBlKHaocIUCwQxS2hASCxABQ3D1T4URFicR8AvjfPICuRRkoAAAAASUVORK5CYII=',
			'CC04' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WEMYQxmmMDQEIImJtLI2OoQyNCKLBTSKNDg6OrSiiDWINLA2BEwJQHJf1Kppq5auioqKQnIfRF2gA6bewNAQTDuwuQVFDJubByr8qAixuA8AeyXO2JqzrxcAAAAASUVORK5CYII=',
			'0CAF' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7GB0YQxmmMIaGIImxBrA2OoQyOiCrE5ki0uDo6IgiFtAq0sDaEAgTAzspaum0VUtXRYZmIbkPTR1CLDQQww5XNHUgt6CLgdyMbt5AhR8VIRb3AQDn68p4kc/Y3AAAAABJRU5ErkJggg==',
			'5417' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7QkMYWhmmMIaGIIkFNDBMZQhhaBBBFQtlRBMLDGB0ZZgCloO7L2za0qWrpq1amYXsvlYRoB1Ae5BtbhUNdZgC0o1kRyvILQwByGIiU8Duc0AWYw1gaGUMdUQRG6jwoyLE4j4AFo3LDxzlktoAAAAASUVORK5CYII=',
			'6C35' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WAMYQ0EwAElMZApro2ujowOyuoAWkQaHhkBUsQaRBoZGR1cHJPdFRk1btWrqyqgoJPeFTAGpcwCpRuhtFYGZgCIGsgNZDOIWhwBk90HczDDVYRCEHxUhFvcBAIPwzXAARjS8AAAAAElFTkSuQmCC',
			'9270' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeUlEQVR4nM2QsQ2AMAwEP4V7irCPKei/gCWYIimyQWADCjIlIISUCEqQ4q98suyTkR7lUFN+8ROaQUaGnNkoR89ZM8ZgvTqSBYNX36nN/JY5rWndpiXzkx4R0dxzVwIIlqwJRo2iuHG4OHEoXITt2J8bKvjfh3nx2wER18u7zEIyUgAAAABJRU5ErkJggg=='        
        );
        $this->text = array_rand( $images );
        return $images[ $this->text ] ;    
    }
    
    function out_processing_gif(){
        $image = dirname(__FILE__) . '/processing.gif';
        $base64_image = "R0lGODlhFAAUALMIAPh2AP+TMsZiALlcAKNOAOp4ANVqAP+PFv///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgAIACwAAAAAFAAUAAAEUxDJSau9iBDMtebTMEjehgTBJYqkiaLWOlZvGs8WDO6UIPCHw8TnAwWDEuKPcxQml0Ynj2cwYACAS7VqwWItWyuiUJB4s2AxmWxGg9bl6YQtl0cAACH5BAUKAAgALAEAAQASABIAAAROEMkpx6A4W5upENUmEQT2feFIltMJYivbvhnZ3Z1h4FMQIDodz+cL7nDEn5CH8DGZhcLtcMBEoxkqlXKVIgAAibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkphaA4W5upMdUmDQP2feFIltMJYivbvhnZ3V1R4BNBIDodz+cL7nDEn5CH8DGZAMAtEMBEoxkqlXKVIg4HibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpjaE4W5tpKdUmCQL2feFIltMJYivbvhnZ3R0A4NMwIDodz+cL7nDEn5CH8DGZh8ONQMBEoxkqlXKVIgIBibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpS6E4W5spANUmGQb2feFIltMJYivbvhnZ3d1x4JMgIDodz+cL7nDEn5CH8DGZgcBtMMBEoxkqlXKVIggEibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpAaA4W5vpOdUmFQX2feFIltMJYivbvhnZ3V0Q4JNhIDodz+cL7nDEn5CH8DGZBMJNIMBEoxkqlXKVIgYDibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpz6E4W5tpCNUmAQD2feFIltMJYivbvhnZ3R1B4FNRIDodz+cL7nDEn5CH8DGZg8HNYMBEoxkqlXKVIgQCibbK9YLBYvLtHH5K0J0IACH5BAkKAAgALAEAAQASABIAAAROEMkpQ6A4W5spIdUmHQf2feFIltMJYivbvhnZ3d0w4BMAIDodz+cL7nDEn5CH8DGZAsGtUMBEoxkqlXKVIgwGibbK9YLBYvLtHH5K0J0IADs=";
        $binary = is_file($image) ? join("",file($image)) : base64_decode($base64_image); 
        header("Cache-Control: post-check=0, pre-check=0, max-age=0, no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: image/gif");
        echo $binary;
    }

}
# end of class phpfmgImage
# ------------------------------------------------------
# end of module : captcha


# module user
# ------------------------------------------------------
function phpfmg_user_isLogin(){
    return ( isset($_SESSION['authenticated']) && true === $_SESSION['authenticated'] );
}


function phpfmg_user_logout(){
    session_destroy();
    header("Location: admin.php");
}

function phpfmg_user_login()
{
    if( phpfmg_user_isLogin() ){
        return true ;
    };
    
    $sErr = "" ;
    if( 'Y' == $_POST['formmail_submit'] ){
        if(
            defined( 'PHPFMG_USER' ) && strtolower(PHPFMG_USER) == strtolower($_POST['Username']) &&
            defined( 'PHPFMG_PW' )   && strtolower(PHPFMG_PW) == strtolower($_POST['Password']) 
        ){
             $_SESSION['authenticated'] = true ;
             return true ;
             
        }else{
            $sErr = 'Login failed. Please try again.';
        }
    };
    
    // show login form 
    phpfmg_admin_header();
?>
<form name="frmFormMail" action="" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:380px;height:260px;">
<fieldset style="padding:18px;" >
<table cellspacing='3' cellpadding='3' border='0' >
	<tr>
		<td class="form_field" valign='top' align='right'>Email :</td>
		<td class="form_text">
            <input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" class='text_box' >
		</td>
	</tr>

	<tr>
		<td class="form_field" valign='top' align='right'>Password :</td>
		<td class="form_text">
            <input type="password" name="Password"  value="" class='text_box'>
		</td>
	</tr>

	<tr><td colspan=3 align='center'>
        <input type='submit' value='Login'><br><br>
        <?php if( $sErr ) echo "<span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
        <a href="admin.php?mod=mail&func=request_password">I forgot my password</a>   
    </td></tr>
</table>
</fieldset>
</div>
<script type="text/javascript">
    document.frmFormMail.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();
}


function phpfmg_mail_request_password(){
    $sErr = '';
    if( $_POST['formmail_submit'] == 'Y' ){
        if( strtoupper(trim($_POST['Username'])) == strtoupper(trim(PHPFMG_USER)) ){
            phpfmg_mail_password();
            exit;
        }else{
            $sErr = "Failed to verify your email.";
        };
    };
    
    $n1 = strpos(PHPFMG_USER,'@');
    $n2 = strrpos(PHPFMG_USER,'.');
    $email = substr(PHPFMG_USER,0,1) . str_repeat('*',$n1-1) . 
            '@' . substr(PHPFMG_USER,$n1+1,1) . str_repeat('*',$n2-$n1-2) . 
            '.' . substr(PHPFMG_USER,$n2+1,1) . str_repeat('*',strlen(PHPFMG_USER)-$n2-2) ;


    phpfmg_admin_header("Request Password of Email Form Admin Panel");
?>
<form name="frmRequestPassword" action="admin.php?mod=mail&func=request_password" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:580px;height:260px;text-align:left;">
<fieldset style="padding:18px;" >
<legend>Request Password</legend>
Enter Email Address <b><?php echo strtoupper($email) ;?></b>:<br />
<input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" style="width:380px;">
<input type='submit' value='Verify'><br>
The password will be sent to this email address. 
<?php if( $sErr ) echo "<br /><br /><span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
</fieldset>
</div>
<script type="text/javascript">
    document.frmRequestPassword.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();    
}


function phpfmg_mail_password(){
    phpfmg_admin_header();
    if( defined( 'PHPFMG_USER' ) && defined( 'PHPFMG_PW' ) ){
        $body = "Here is the password for your form admin panel:\n\nUsername: " . PHPFMG_USER . "\nPassword: " . PHPFMG_PW . "\n\n" ;
        if( 'html' == PHPFMG_MAIL_TYPE )
            $body = nl2br($body);
        mailAttachments( PHPFMG_USER, "Password for Your Form Admin Panel", $body, PHPFMG_USER, 'You', "You <" . PHPFMG_USER . ">" );
        echo "<center>Your password has been sent.<br><br><a href='admin.php'>Click here to login again</a></center>";
    };   
    phpfmg_admin_footer();
}


function phpfmg_writable_check(){
 
    if( is_writable( dirname(PHPFMG_SAVE_FILE) ) && is_writable( dirname(PHPFMG_EMAILS_LOGFILE) )  ){
        return ;
    };
?>
<style type="text/css">
    .fmg_warning{
        background-color: #F4F6E5;
        border: 1px dashed #ff0000;
        padding: 16px;
        color : black;
        margin: 10px;
        line-height: 180%;
        width:80%;
    }
    
    .fmg_warning_title{
        font-weight: bold;
    }

</style>
<br><br>
<div class="fmg_warning">
    <div class="fmg_warning_title">Your form data or email traffic log is NOT saving.</div>
    The form data (<?php echo PHPFMG_SAVE_FILE ?>) and email traffic log (<?php echo PHPFMG_EMAILS_LOGFILE?>) will be created automatically when the form is submitted. 
    However, the script doesn't have writable permission to create those files. In order to save your valuable information, please set the directory to writable.
     If you don't know how to do it, please ask for help from your web Administrator or Technical Support of your hosting company.   
</div>
<br><br>
<?php
}


function phpfmg_log_view(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    
    phpfmg_admin_header();
   
    $file = $files[$n];
    if( is_file($file) ){
        if( 1== $n ){
            echo "<pre>\n";
            echo join("",file($file) );
            echo "</pre>\n";
        }else{
            $man = new phpfmgDataManager();
            $man->displayRecords();
        };
     

    }else{
        echo "<b>No form data found.</b>";
    };
    phpfmg_admin_footer();
}


function phpfmg_log_download(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );

    $file = $files[$n];
    if( is_file($file) ){
        phpfmg_util_download( $file, PHPFMG_SAVE_FILE == $file ? 'form-data.csv' : 'email-traffics.txt', true, 1 ); // skip the first line
    }else{
        phpfmg_admin_header();
        echo "<b>No email traffic log found.</b>";
        phpfmg_admin_footer();
    };

}


function phpfmg_log_delete(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    phpfmg_admin_header();

    $file = $files[$n];
    if( is_file($file) ){
        echo unlink($file) ? "It has been deleted!" : "Failed to delete!" ;
    };
    phpfmg_admin_footer();
}


function phpfmg_util_download($file, $filename='', $toCSV = false, $skipN = 0 ){
    if (!is_file($file)) return false ;

    set_time_limit(0);


    $buffer = "";
    $i = 0 ;
    $fp = @fopen($file, 'rb');
    while( !feof($fp)) { 
        $i ++ ;
        $line = fgets($fp);
        if($i > $skipN){ // skip lines
            if( $toCSV ){ 
              $line = str_replace( chr(0x09), ',', $line );
              $buffer .= phpfmg_data2record( $line, false );
            }else{
                $buffer .= $line;
            };
        }; 
    }; 
    fclose ($fp);
  

    
    /*
        If the Content-Length is NOT THE SAME SIZE as the real conent output, Windows+IIS might be hung!!
    */
    $len = strlen($buffer);
    $filename = basename( '' == $filename ? $file : $filename );
    $file_extension = strtolower(substr(strrchr($filename,"."),1));

    switch( $file_extension ) {
        case "pdf": $ctype="application/pdf"; break;
        case "exe": $ctype="application/octet-stream"; break;
        case "zip": $ctype="application/zip"; break;
        case "doc": $ctype="application/msword"; break;
        case "xls": $ctype="application/vnd.ms-excel"; break;
        case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
        case "gif": $ctype="image/gif"; break;
        case "png": $ctype="image/png"; break;
        case "jpeg":
        case "jpg": $ctype="image/jpg"; break;
        case "mp3": $ctype="audio/mpeg"; break;
        case "wav": $ctype="audio/x-wav"; break;
        case "mpeg":
        case "mpg":
        case "mpe": $ctype="video/mpeg"; break;
        case "mov": $ctype="video/quicktime"; break;
        case "avi": $ctype="video/x-msvideo"; break;
        //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
        case "php":
        case "htm":
        case "html": 
                $ctype="text/plain"; break;
        default: 
            $ctype="application/x-download";
    }
                                            

    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer");
    //Use the switch-generated Content-Type
    header("Content-Type: $ctype");
    //Force the download
    header("Content-Disposition: attachment; filename=".$filename.";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$len);
    
    while (@ob_end_clean()); // no output buffering !
    flush();
    echo $buffer ;
    
    return true;
 
    
}
?>