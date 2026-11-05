<?php
require_once __DIR__ . '/../core/AiService.php';
class AiTutorController {
 public function index(): void {
  if(empty($_SESSION['user_id'])){header('Location: '.BASE_URL.'/login');exit;}
  require __DIR__.'/../views/ai_tutor.php';
 }
 public function ask(): void {
  header('Content-Type: application/json; charset=utf-8');
  if(empty($_SESSION['user_id'])){http_response_code(401);echo json_encode(['success'=>false,'message'=>'Devam etmek için giriş yapmalısın.']);exit;}
  if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){http_response_code(405);echo json_encode(['success'=>false,'message'=>'Geçersiz istek.']);exit;}
  $prompt=trim((string)($_POST['prompt']??''));
  if($prompt===''||mb_strlen($prompt)>2000){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Mesaj 1-2000 karakter arasında olmalı.']);exit;}
  $moderation=ModerationService::check($prompt);
  if(strtolower($moderation['status']??'approved')==='rejected'){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Mesajın içerik kurallarına uygun değil.']);exit;}
  $answer=AiService::getHint('Öğrencinin genel ders ve konu sorusu','Öğrenci bir ders konusu, kavram veya ödev hakkında yardım istiyor. Önce seviyesini gözeterek konuyu açıkla; ardından basit bir örnek ve öğrencinin kendini deneyebileceği kısa bir soru ver. Gerektiğinde çözümü adım adım açıkla.',$prompt);
  echo json_encode(['success'=>true,'answer'=>$answer],JSON_UNESCAPED_UNICODE);
  exit;
 }
}
