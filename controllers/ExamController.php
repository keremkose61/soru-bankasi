<?php
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Bookmark.php';
require_once __DIR__ . '/../models/Answer.php';

class ExamController {
    private $questionModel;
    private $bookmarkModel;
    private $answerModel;

    public function __construct() {
        $this->questionModel = new Question();
        $this->bookmarkModel = new Bookmark();
        $this->answerModel   = new Answer();
    }

    public function booklet() {
        $subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';
        $source  = isset($_GET['source']) ? trim($_GET['source']) : '';
        $idsParam = isset($_GET['ids']) ? trim($_GET['ids']) : '';

        $title = "Genel Deneme Sınavı";
        $questions = [];

        if (!empty($idsParam)) {
            $ids = array_map('intval', explode(',', $idsParam));
            $questions = $this->questionModel->getByIds($ids);
            $title = "Özel Seçilmiş Sorular Testi";
        } elseif ($source === 'bookmarks' && isset($_SESSION['user_id'])) {
            $questions = $this->bookmarkModel->getBookmarksByUserId((int)$_SESSION['user_id']);
            $title = "Favorilerim Deneme Kitapçığı";
        } elseif (!empty($subject)) {
            $questions = $this->questionModel->getAll('newest', $subject);
            $title = htmlspecialchars($subject) . " Branş Tarama Testi";
        } else {
            $questions = $this->questionModel->getAll('newest');
            $title = "Karma Deneme ve Soru Kitapçığı";
        }

        // En fazla 50 soru ile sınırla
        $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
        $questions = array_slice($questions, 0, $limit);

        // Cevap anahtarı ve en iyi çözümleri iliştir
        foreach ($questions as &$q) {
            $qAnswers = $this->answerModel->getAnswersByQuestionId($q['id']);
            $q['best_answer'] = null;
            foreach ($qAnswers as $qa) {
                if (!empty($qa['is_best_answer'])) {
                    $q['best_answer'] = $qa;
                    break;
                }
            }
            if (!$q['best_answer'] && !empty($qAnswers)) {
                $q['best_answer'] = $qAnswers[0];
            }
        }
        unset($q);

        require __DIR__ . '/../views/exam_booklet.php';
    }
}
