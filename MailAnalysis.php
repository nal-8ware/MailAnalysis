<?php
/**
 * ポートフォリオ用に作成中のメール解析クラス
 * 以前業務で製作したうろ覚えの物を作り直し
 * 
 * @version 0.1
 * @author Kazuki Tsuchiya
 * @link https://portfolio.forkwell.com/@8warebase
 * 
 */
class MailAnalysis
{
    /**
    * 
    *
    * @var string $mailHead         メールヘッダ部分格納先
    * @var string $mailData         メールデータ部分格納先
    * @var array  $analysisHead     メールヘッダ部分解析結果
    * @var array  $analysisData     メールデータ部分解析結果
    * 
    * @var bool   DEBUG_FRAG        true: デバッグモード false: 通常モード
    * @var array  debug_massage     
    */
    private $mailSorce;
    private $analysisHead, $analysisData;

    private $debugMode = false;
    private $debugMassage = ["[MailAnalysis debug]",];

    /**
    * コンストラクタ
    *
    * @param string $mail_source メースのソース
    */
    public function __construct($mailSource, $debugMode = false) {
        $this->debugMode = ($debugMode != false)? true: false;
        $this->debug("__construct()","start");

        $mailSource = str_replace(array("\r\n","\r"), "\n", $mailSource); // 念の為改行コードを\nに統一
        $this->mailSorce = explode("\n\n", $mailSource, 2);
        $this->HeaderAnalysis();
    }

    /**
    * ヘッダ部分の解析データを返す
    *
    * @param string $key
    *       対応したkeyが無い時  false
    *
    *       To          string  受信アドレス
    *       ToName      string  受信者名 nullの場合有り
    *       From        string  送信アドレス
    *       FromName    string  送信者名 nullの場合有り
    *       Subject     string  件名
    *       Return-Path string  返信アドレス
    *       Encoding    string  Content-Transfer-Encoding 7bit,8bit,base64,quoted-printable,binary
    *       Type        string  Content-Type  text/plain,multipart/alternative
    *       Charset     string  文字コード
    *       Multipart   bool true:マルチパート false:テキストのみのメール
    */
    public function GetHeader(string $key) {
        return isset($this->analysisHead[$key])? $this->analysisHead[$key]: false;
    }

    public function GetReturnPath() {
        return $this->GetHeader("ReturnPath");
    }

    public function GetTo() {
        return $this->GetHeader("To");
    }

    public function GetToName() {
        return $this->GetHeader("ToName");
    }

    public function GetFrom() {
        return $this->GetHeader("From");
    }

    public function GetFromName() {
        return $this->GetHeader("FromName");
    }

    public function GetSubject() {
        return $this->GetHeader("Subject");
    }

    public function GetMultipart() {
        return $this->GetHeader("Multipart");
    }

    /**
    * データ部分の解析結果を返す
    *
    * @param string $key
    *       対応したkeyが無い時  false
    *       TEXT        string  本文テキスト
    *
    *       以下はマルチパートメールの時のみ
    *           Html        string  本文HTML
    *           images      array   バイナリデータ
    */
    /*
    public function GetData(string $key) {
        return isset($this->analysisData[$key])? $this->analysisData[$key]: false;
    }

    public function GetText() {
        return $this->GetData("text");
    }

    public function GetHtml() {
        return $this->GetData("Html");
    }

    public function GetImagesNum() {
        if(is_array($this->analysisData[$key])) {
            rertrn count($this->analysisData[$key]);
        }
        else {
            return 0;
        }
    }

    public function GetImages() {
        return $this->GetData("images");
    }

    public function GetImage(int $num) {
        return isset($this->analysisData["images"][$num])? $this->analysisData["images"][$num]: false;
    }
    */

    /**
    * ヘッダ部分の解析
    */
    public function HeaderAnalysis() {
        $this->debug("HeaderAnalysis()");
        $header = &$this->mailSorce[0]; 
        $set = &$this->analysisHead;

        $common1 = '\s*(.+?)$';                     // 正規表現共通部分 １行
        $common2 = '\s*((.+?)$(\s+?\S+?$)*)';       // 正規表現共通部分 複数行
       
        // Return-Path:
        $this->debug("-- Return-Path:");
        if(preg_match("/^Return-Path:".$common1."/m", $header, $matchs) > 0){
            $set['ReturnPath'] = $matchs[1];
        }

        // To:
        $this->debug("-- To check");
        if(preg_match("/^To:".$common2."/m", $header, $matchs) > 0){
            $toLine = $matchs[1];
            preg_match("/^(.+)?\s*?<(.*)>$/s", $toLine, $matchs);
            if(isset($matchs[2])) {
                $set['To'] = $matchs[2];
                $set['ToName'] = mb_decode_mimeheader($matchs[1]);
                $this->debug("  -- ",$set['To']);
                $this->debug("  -- ToName:",$set['ToName']);
            }
            else {
                $set['To'] = $toLine;
                $this->debug("  -- ",$set['To']);
            }
        }

        $this->debug("-- From:");
        if(preg_match("/^From:".$common2."/m", $header, $matchs) > 0){
            $toLine = $matchs[1];
            preg_match("/^(.+)?\s*?<(.*)>$/s", $toLine, $matchs);
            if(isset($matchs[2])) {
                $set['From'] = $matchs[2];
                $set['FromName'] = mb_decode_mimeheader($matchs[1]);
                $this->debug("  -- ",$set['From']);
                $this->debug("  -- FromName:",$set['FromName']);
            }
            else {
                $set['From'] = $toLine;
                $this->debug("  -- From:",$set['From']);
            }
        }

        $this->debug("-- Subject:");
        if(preg_match("/^Subject:".$common2."/m", $header, $matchs) > 0){
            $set['Subject'] = mb_decode_mimeheader($matchs[1]);
            $this->debug("  -- ",$set['Subject']);
        }

        // Encoding:
        $this->debug("-- Encoding:");
        if(preg_match("/^Content-Transfer-Encoding:".$common1."/im", $header, $matchs) > 0){
            $set['Encoding'] = $matchs[1];
            $this->debug("  -- ",$set['Encoding']);
        }

        // Type:
        $this->debug("-- Type:");
        if(preg_match("/^Content-Type:\s*(.+?\/.+?);\s*charset=(.+?)$/im", $header, $matchs) > 0){
            $set['Type'] = $matchs[1];
            $set['Multipart'] = ($set['Type'] == 'multipart/alternative')? true: false;
            $set['Charset'] = $matchs[1];
            $this->debug("  -- ",$set['Type']);
            $this->debug("  -- Multipart:",($set['Multipart']? "true":"false"));
            $this->debug("  -- Charset:",$set['Charset']);
        }

        /*
        *       Type        string  Content-Type  text/plain,multipart/alternative
        *       Charset     string  文字コード
        *       Multipart   bool true:マルチパート false:テキストのみのメール
        */
    
    }

    /**
    * データ部分の解析
    */
    /*
    public function HeaderAnalysis() {
        
    }
    */
    
    /**
    * デバッグ
    */
    public function Debug($label,$str=null) {
        if($this->debugMode){
            $trace = debug_backtrace();
            $value = &$trace[0];
            $this->debugMassage[] = basename($value["file"])."(".$value["line"]."): ".$label." ".$str;
        }
    }

    public function PrintDebug() {
        if($this->debugMode){
            if(is_array($this->debugMassage)) {
                echo '<pre style="background-color: #ddd; text-align: left;">'.implode("\n",$this->debugMassage)."</pre>";
            }
        }
    }

    public function GetDebug() {
        if($this->debugMode) {
            if(is_array($this->debugMassage)) {
                return implode("\n",$this->debugMassage);
            }
        }
    }
}
?>