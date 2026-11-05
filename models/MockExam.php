<?php
require_once __DIR__ . '/Database.php';
class MockExam {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }
    public function subjects(): array {
        return $this->db->query("SELECT DISTINCT subject FROM mock_exam_questions WHERE is_active=1 ORDER BY subject")->fetchAll(PDO::FETCH_COLUMN);
    }
    public function count(?string $subject = null): int {
        if ($subject) { $s=$this->db->prepare("SELECT COUNT(*) FROM mock_exam_questions WHERE is_active=1 AND subject=?"); $s->execute([$subject]); return (int)$s->fetchColumn(); }
        return (int)$this->db->query("SELECT COUNT(*) FROM mock_exam_questions WHERE is_active=1")->fetchColumn();
    }
    public function history(int $uid): array {
        $s=$this->db->prepare("SELECT * FROM mock_exam_attempts WHERE user_id=? AND status='completed' ORDER BY submitted_at DESC LIMIT 10");
        $s->execute([$uid]); return $s->fetchAll();
    }
    public function createQuestion(array $d, int $uid): void {
        $s=$this->db->prepare("INSERT INTO mock_exam_questions(subject,topic,question_text,option_a,option_b,option_c,option_d,option_e,correct_option,explanation,difficulty,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
        $s->execute([$d['subject'],$d['topic'] ?: null,$d['question_text'],$d['option_a'],$d['option_b'],$d['option_c'],$d['option_d'],$d['option_e'] ?: null,$d['correct_option'],$d['explanation'] ?: null,$d['difficulty'],$uid]);
    }
    public function start(int $uid, string $title, ?string $subject, int $limit, int $minutes): int {
        $sql="SELECT * FROM mock_exam_questions WHERE is_active=1"; $p=[];
        if ($subject) { $sql.=" AND subject=?"; $p[]=$subject; }
        $sql.=" ORDER BY RAND() LIMIT ".(int)$limit;
        $s=$this->db->prepare($sql); $s->execute($p); $qs=$s->fetchAll();
        if (!$qs) throw new RuntimeException('Bu seçim için henüz soru bulunmuyor.');
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare("INSERT INTO mock_exam_attempts(user_id,title,subject,question_count,duration_minutes) VALUES(?,?,?,?,?)");
            $s->execute([$uid,$title,$subject ?: null,count($qs),$minutes]); $id=(int)$this->db->lastInsertId();
            $a=$this->db->prepare("INSERT INTO mock_exam_attempt_answers(attempt_id,question_id,question_text_snapshot,option_a_snapshot,option_b_snapshot,option_c_snapshot,option_d_snapshot,option_e_snapshot,correct_option_snapshot,explanation_snapshot) VALUES(?,?,?,?,?,?,?,?,?,?)");
            foreach($qs as $q) $a->execute([$id,$q['id'],$q['question_text'],$q['option_a'],$q['option_b'],$q['option_c'],$q['option_d'],$q['option_e'],$q['correct_option'],$q['explanation']]);
            $this->db->commit(); return $id;
        } catch(Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function attempt(int $id, int $uid): ?array {
        $s=$this->db->prepare("SELECT * FROM mock_exam_attempts WHERE id=? AND user_id=?"); $s->execute([$id,$uid]); $a=$s->fetch();
        if (!$a) return null;
        $s=$this->db->prepare("SELECT * FROM mock_exam_attempt_answers WHERE attempt_id=? ORDER BY id"); $s->execute([$id]); $a['questions']=$s->fetchAll(); return $a;
    }
    public function submit(int $id, int $uid, array $input): bool {
        $a=$this->attempt($id,$uid); if(!$a || $a['status']!=='in_progress') return false;
        if(time()>strtotime($a['started_at'])+(int)$a['duration_minutes']*60) $input=[];
        $correct=$wrong=$blank=0; $this->db->beginTransaction();
        try {
            $u=$this->db->prepare("UPDATE mock_exam_attempt_answers SET selected_option=?,is_correct=? WHERE id=? AND attempt_id=?");
            foreach($a['questions'] as $q) {
                $v=strtoupper((string)($input[(string)$q['id']] ?? ''));
                if(!in_array($v,['A','B','C','D','E'],true)) $v='';
                $ok=($v!=='' && $v===$q['correct_option_snapshot']) ? 1 : 0;
                if($v==='') $blank++; elseif($ok) $correct++; else $wrong++;
                $u->execute([$v ?: null,$ok,$q['id'],$id]);
            }
            $net=$correct-($wrong/4); $pct=$correct/max(1,(int)$a['question_count'])*100;
            $u=$this->db->prepare("UPDATE mock_exam_attempts SET correct_count=?,wrong_count=?,blank_count=?,net_score=?,score_percent=?,status='completed',submitted_at=NOW() WHERE id=? AND user_id=? AND status='in_progress'");
            $u->execute([$correct,$wrong,$blank,$net,$pct,$id,$uid]); $this->db->commit(); return true;
        } catch(Throwable $e) { $this->db->rollBack(); throw $e; }
    }
}
