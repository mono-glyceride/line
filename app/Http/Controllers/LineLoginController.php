<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineLoginController extends Controller
{

  public function lineLogin()
  {
      // CSRF防止のためランダムな英数字を生成
      $state = Str::random(32);

      // リプレイアタックを防止するためランダムな英数字を生成
      $nonce  = Str::random(32);
      
      //config/line.phpで定義した環境変数を呼び出し
      $line_channel_id = config('line.line_channel_id');
    
      $uri ="https://access.line.me/oauth2/v2.1/authorize?";
      $response_type = "response_type=code";
      //LINEログインチャネルID
      $client_id = "&client_id=$line_channel_id";
      //コールバックURL
      $redirect_uri ="&redirect_uri=https://gurido-line.herokuapp.com//callback";
      $state_uri = "&state=".$state;
      $scope = "&scope=openid%20profile";
      $prompt = "&prompt=consent";
      $nonce_uri = "&nonce=";

      $uri = $uri . $response_type . $client_id . $redirect_uri . $state_uri . $scope . $prompt . $nonce_uri;

      return redirect($uri);

  }
  
  public function callback(Request $request)
  {

    //LINEからアクセストークンを取得
    $accessToken = $this->getAccessToken($request);
    //プロフィール取得
    $profile = $this->getProfile($accessToken);
    //$profileには名前（displayName）、ID（userId）、アイコン画像URL（pictureUrl）などの情報が含まれる
    $userId = $profile->userId;
    //メッセージ送信
    $this->sendMessage($userId, 'auto');

    return view('callback', ['userId'=>$userId]);

  }

  public function getAccessToken($req)
  {

    //config/line.phpで定義した環境変数を呼び出し
    $line_channel_id = config('line.line_channel_id');
    $line_channel_secret = config('line.line_channel_secret');
    
      
    $headers = [ 'Content-Type: application/x-www-form-urlencoded' ];
    $post_data = array(
      'grant_type'    => 'authorization_code',
      'code'          => $req['code'],
      'redirect_uri'  => 'https://gurido-line.herokuapp.com//callback',
      'client_id'     => $line_channel_id,
      'client_secret' => $line_channel_secret,
      'prompt'        => 'consent',
      'bot_prompt'    => 'aggressive'
    );
    $url = 'https://api.line.me/oauth2/v2.1/token';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

    $res = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($res);
    $accessToken = $json->access_token;

    return $accessToken;

  }

  public function getProfile($at)
  {

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $at));
    curl_setopt($curl, CURLOPT_URL, 'https://api.line.me/v2/profile');
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $res = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($res);

    return $json;

  }

  public function sendMessage($userId, $message_flg) {
    
    //Messaging APIチャネルのアクセストークン取得
    $headers = [ 'Content-Type: application/x-www-form-urlencoded' ];
    
    //config/line.phpで定義した環境変数を呼び出し
    $line_message_channel_id = config('line.line_message_channel_id');
    $line_message_channel_secret = config('line.line_message_channel_secret');
    
    $post_data = array(
      'grant_type'    => 'client_credentials',
      //Messaging APIチャネルID
      'client_id'     => $line_message_channel_id,
      //Messaging APIチャネルシークレット
      'client_secret' => $line_message_channel_secret
    );
    $url = 'https://api.line.me/v2/oauth/accessToken';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

    $res = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($res);
    $accessToken = $json->access_token;

    //Messaging APIチャネルからtestメッセージを送信
    $messenger = new LINEBot(
        new CurlHTTPClient($accessToken),
        [
            'channelSecret' => $line_message_channel_secret
        ]
    );
    
    if($message_flg == 'auto'){
      $text = "LINEログインを行いました";
    }
    else{
      $randNum = rand(1, 4);
      
      switch($randNum){
        case 1:
          $text = "呪術廻戦の興行収入は８５億を突破しました！";
          break;
        case 2:
          $text = "五条悟は菅原道真の子孫です";
          break;
        case 3:
          $text = "冥冥さんには年の離れた実弟がいます";
          break;
        case 4:
          $text = "夏油傑は唯一の特級呪詛師です。……一般的には";
          break;
      }
    }

    $messenger->pushMessage($userId, new TextMessageBuilder($text));

  }
  
  public function clickBtn(Request $request){
    $userId = $request->userId;
    $this->sendMessage($userId, 'manual');
    return view('callback', ['userId'=>$userId]);
  }
}