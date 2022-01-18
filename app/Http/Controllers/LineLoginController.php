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
    
      $uri ="https://access.line.me/oauth2/v2.1/authorize?";
      $response_type = "response_type=code";
      $client_id = "&client_id=1656807913";
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
    //メッセージ送信
    $this->sendMessage($profile->userId);

    return view('callback', compact('profile'));

  }

  public function getAccessToken($req)
  {

    $headers = [ 'Content-Type: application/x-www-form-urlencoded' ];
    $post_data = array(
      'grant_type'    => 'authorization_code',
      'code'          => $req['code'],
      'redirect_uri'  => 'https://gurido-line.herokuapp.com//callback',
      'client_id'     => '1656807913',
      'client_secret' => 'dc9cf4ea54f53dbd295cdcb44cbae6f6'
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

  public function sendMessage($lineId) {
    
    //Messaging APIチャネルのアクセストークン取得
    $headers = [ 'Content-Type: application/x-www-form-urlencoded' ];
    $post_data = array(
      'grant_type'    => 'client_credentials',
      'client_id'     => '1656814611',
      'client_secret' => '7c9f0fc369324b76800bc2cc43ea758d'
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
            'channelSecret' => '7c9f0fc369324b76800bc2cc43ea758d'
        ]
    );

    $messenger->pushMessage($lineId, new TextMessageBuilder("test"));

  }
}