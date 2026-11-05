<?php
require_once __DIR__ . '/../models/MockExam.php';
class MockExamController {
 private $exam;
 public function __construct(){ $this->exam=new MockExam(); }
 private function userId(){ if(empty($_SESSION['user_id'])){header('Location: '.BASE_URL.'/login');exit;} return (int)$_SESSION['user_id']; }
 public function index(){
  $uid=$this->userId(); $subjects=$this->exam->subjects(); $history=$this->exam->history($uid);
  $questionCount=$this->exam->count(); $canManage=in_array($_SESSION['role']??'', ['teacher','admin'],true);
  require __DIR__.'/../views/mock_exams.php';
 }
 public function createQuestion(){
  $uid=$this->userId();
  if(!in_array($_SESSION['role']??'', ['teacher','admin'],true)){http_response_code(403);exit('Öğretmen yetkisi gerekir.');}
  $error='';
  if($_SERVER['REQUEST_METHOD']==='POST'){
   $d=[]; foreach(['subject','topic','question_text','option_a','option_b','option_c','option_d','option_e','correct_option','explanation','difficulty'] as $k)$d[$k]=trim((string)($_POST[$k]??''));
   if($d['subject']===''||$d['question_text']===''||$d['option_a']===''||$d['option_b']===''||$d['option_c']===''||$d['option_d']===''||!in_array($d['correct_option'],['A','B','C','D','E'],true)||($d['correct_option']==='E'&&$d['option_e']===''))$error='Ders, soru, A-D seçenekleri ve doğru cevap zorunludur.';
   else {if(!in_array($d['difficulty'],['easy','medium','hard'],true))$d['difficulty']='medium';$this->exam->createQuestion($d,$uid);header('Location: '.BASE_URL.'/mock-exams?added=1');exit;}
  }
  require __DIR__.'/../views/mock_exam_question_form.php';
 }
 public function start(){
  $uid=$this->userId(); if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Yalnızca POST.');}
  $subject=trim((string)($_POST['subject']??''));$count=max(5,min(50,(int)($_POST['question_count']??10)));$minutes=max(5,min(180,(int)($_POST['duration']??20)));
  if($this->exam->count($subject?:null)<1){header('Location: '.BASE_URL.'/mock-exams?empty=1');exit;}
  $id=$this->exam->start($uid,$subject?$subject.' Denemesi':'Genel Deneme',$subject?:null,$count,$minutes);
  header('Location: '.BASE_URL.'/mock-exams/take/'.$id);exit;
 }
 public function take($id){
  $uid=$this->userId();$attempt=$this->exam->attempt((int)$id,$uid);
  if(!$attempt){http_response_code(404);exit('Deneme bulunamadı.');}
  if($attempt['status']==='completed'){header('Location: '.BASE_URL.'/mock-exams/result/'.$id);exit;}
  $remaining=max(0,strtotime($attempt['started_at'])+(int)$attempt['duration_minutes']*60-time());
  if(!$remaining){$this->exam->submit((int)$id,$uid,[]);header('Location: '.BASE_URL.'/mock-exams/result/'.$id);exit;}
  require __DIR__.'/../views/mock_exam_take.php';
 }
 public function submit($id){
  $uid=$this->userId();if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Yalnızca POST.');}
  $answers=is_array($_POST['answers']??null)?$_POST['answers']:[];
  $this->exam->submit((int)$id,$uid,$answers);header('Location: '.BASE_URL.'/mock-exams/result/'.$id);exit;
 }
 public function result($id){
  $uid=$this->userId();$attempt=$this->exam->attempt((int)$id,$uid);
  if(!$attempt||$attempt['status']!=='completed'){header('Location: '.BASE_URL.'/mock-exams');exit;}
  require __DIR__.'/../views/mock_exam_result.php';
 }
}
