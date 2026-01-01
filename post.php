<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /index.php");
    exit;
}

$post = get_post($id);
if (!$post) {
    header("Location: /404.php");
    exit;
}

$pageTitle = $post['title'];
require 'includes/templates/header.php';
?>

<div class="content">

<div class="e2-note">

  <article>
    <h1 class="e2-smart-title"><?= htmlspecialchars($post['title']) ?></h1>

    <div class="e2-note-text e2-text">
      <?= markdown($post['content']) ?>
    </div>
  </article>

  <div class="e2-band e2-band-meta-size e2-note-meta">
    <div class="e2-band-scrollable js-band-scrollable">
      <div class="js-band-scrollable-inner">
        <nav>
          <div class="band-item">
            <div class="band-item-inner">
              <span title="<?= date('d F Y, H:i', strtotime($post['created_at'])) ?>"><?= time_ago($post['created_at']) ?></span>
            </div>
          </div>
          
          <?php if (!empty($post['tags'])): ?>
            <?php foreach (parse_tags($post['tags']) as $tag): ?>
              <div class="band-item">
                <a href="/tags.php?tag=<?= urlencode($tag) ?>" class="e2-tag band-item-inner"><?= htmlspecialchars($tag) ?></a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </nav>
      </div>
    </div>
  </div>

</div>

<a name="comments"></a>

<?php if (isset($_GET['comment']) && $_GET['comment'] === 'pending'): ?>
  <div class="moderation-notice">
    Ваш коментар надіслано на модерацію. Він з'явиться після перевірки адміністратором.
  </div>
<?php endif; ?>

<?php
$comments = get_comments($post['id']);
$comment_count = count($comments);
?>

<div class="e2-comments">
  
  <?php if ($comment_count > 0): ?>
    <div class="e2-section-heading">
      <span id="e2-comments-count">
        <?= $comment_count ?> 
        <?php if ($comment_count == 1): ?>
          коментар
        <?php elseif ($comment_count < 5): ?>
          коментарі
        <?php else: ?>
          коментарів
        <?php endif; ?>
      </span>
    </div>

    <?php foreach ($comments as $comment): ?>
      <a name="comment-<?= $comment['id'] ?>"></a>
      
      <div class="e2-comment-and-reply">
        <div>
          <div class="e2-comment">
            <div class="e2-comment-userpic-area">
              <div class="e2-comment-userpic-area-inner">
                <div class="e2-comment-userpic-area-inner-placeholder">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" xml:space="preserve">
                    <circle fill="none" cx="20" cy="20" r="19.5"/>
                    <path stroke="none" d="m33.009 33.775-.66-.327c-1.905-.544-3.805-1.104-5.715-1.627-2.121-.58-2.958-1.511-2.557-3.646.349-1.86 1.183-3.627 1.766-5.447.403-1.259 1.265-2.668.989-3.778-.398-1.603-.046-3.015.045-4.518.123-2.023-.255-3.987-2.162-5.055C23.196 8.529 21.61 7.984 20 8c-1.61-.016-3.196.528-4.714 1.378-1.907 1.068-2.285 3.032-2.162 5.055.091 1.503.443 2.914.045 4.518-.276 1.11.586 2.519.989 3.778.583 1.82 1.417 3.586 1.766 5.447.401 2.134-.436 3.066-2.557 3.646-1.911.522-3.811 1.083-5.715 1.627l-.66.327-.295 1.254C9.24 37.341 13.461 40 20 40s10.76-2.659 13.304-4.971l-.295-1.254z"/>
                  </svg>
                </div>
              </div>
            </div>

            <div class="e2-comment-content-area">
              <span class="e2-comment-author e2-comment-piece-markable">
                <span><?= htmlspecialchars($comment['author']) ?></span>
              </span>
              <span class="e2-comment-date" title="<?= date('d F Y, H:i', strtotime($comment['created_at'])) ?>">
                <?= time_ago($comment['created_at']) ?>
              </span>

              <div class="e2-comment-content e2-text">
                <?= nl2br(htmlspecialchars($comment['content'])) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <form action="/add-comment.php" method="POST" accept-charset="UTF-8" name="form-comment" id="form-comment">
    
    <div class="e2-section-heading">Ваш коментар</div>

    <input type="hidden" name="post_id" value="<?= $post['id'] ?>" />
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>" />

    <div class="form">
      
      <div class="form-control">
        <textarea name="content" class="required width-4 height-8 e2-textarea-autosize" id="text" tabindex="1" required></textarea>
      </div>

      <div class="form-control">
        <div class="form-label input-label"><label>Ім'я та прізвище</label></div>
        <div class="form-element">
          <input type="text" class="text required width-2" tabindex="2" id="name" name="author" required />
        </div>
      </div>

      <div class="form-control">
        <button type="submit" id="submit-button" class="e2-button e2-submit-button" tabindex="3">
          Надіслати
        </button>
        <span class="e2-keyboard-shortcut">Ctrl + Enter</span>
      </div>

    </div>

  </form>

</div>

</div>

<?php require 'includes/templates/footer.php'; ?>
