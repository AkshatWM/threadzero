// Insert >>postId into the reply textarea when a user clicks a post number or Quote link
function quotePost(postId) {
    const textarea = document.getElementById('reply-content');
    if (!textarea) return;

    const quoteTag = '>>' + postId + '\n';
    textarea.value += quoteTag;
    textarea.focus();
}