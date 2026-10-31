<?php
/**
 * Central CSRF protection for BrainBoost.
 * Tokens are session-bound and compared in constant time.
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_validate_request')) {
    function csrf_validate_request() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return true;
        }

        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        $expected = $_SESSION['csrf_token'] ?? '';

        if (!is_string($provided) || !is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            exit('İstek güvenlik doğrulamasından geçemedi. Sayfayı yenileyip tekrar deneyin.');
        }

        return true;
    }
}

if (!function_exists('csrf_inject_html')) {
    function csrf_inject_html($html) {
        if (!is_string($html) || stripos($html, '<html') === false) {
            return $html;
        }

        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        $meta = '<meta name="csrf-token" content="' . $token . '">';

        if (stripos($html, '</head>') !== false && stripos($html, 'name="csrf-token"') === false) {
            $html = preg_replace('/<\/head>/i', $meta . "\n</head>", $html, 1);
        }

        // Add a token to every ordinary HTML POST form, including multipart uploads.
        $html = preg_replace_callback('/<form\\b[^>]*>/i', function ($match) use ($token) {
            $tag = $match[0];
            if (preg_match('/\\bmethod\\s*=\\s*([\'"\\"]?)post\\1/i', $tag)) {
                return $tag . '<input type="hidden" name="csrf_token" value="' . $token . '">';
            }
            return $tag;
        }, $html);

        // Attach the same token to same-origin POST fetch calls used by AJAX features.
        $script = '<script>
(function () {
  var meta = document.querySelector("meta[name=csrf-token]");
  if (!meta || window.__brainBoostCsrfFetchPatched) return;
  window.__brainBoostCsrfFetchPatched = true;
  var token = meta.getAttribute("content");
  var originalFetch = window.fetch.bind(window);
  window.fetch = function (input, init) {
    init = init || {};
    var method = (init.method || (input && input.method) || "GET").toUpperCase();
    if (method === "POST") {
      var url = typeof input === "string" ? input : (input && input.url) || "";
      try {
        var target = new URL(url, window.location.href);
        if (target.origin === window.location.origin) {
          var headers = new Headers(init.headers || (input instanceof Request ? input.headers : undefined));
          headers.set("X-CSRF-Token", token);
          init.headers = headers;
        }
      } catch (e) {}
    }
    return originalFetch(input, init);
  };
})();
</script>';
        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $script . "\n</body>", $html, 1);
        }
        return $html;
    }
}

if (!function_exists('require_post_method')) {
    function require_post_method() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            header('Content-Type: text/plain; charset=utf-8');
            exit('Bu işlem yalnızca POST isteğiyle gerçekleştirilebilir.');
        }
    }
}
