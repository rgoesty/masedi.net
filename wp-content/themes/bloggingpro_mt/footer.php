  </div>
</div>
<!-- container END -->
 
 <div class="ExtraBG">
  <div class="Extra">
  <div class="RecentPosts">
   <h3>Recent Entries</h3>
    <ul><?php if (function_exists('mdv_recent_posts')) { mdv_recent_posts(); } ?></ul>
  </div>
  <div class="LastComments">
   <h3>Recent Comments</h3>
    <ul><?php if (function_exists('mdv_recent_comments')) { mdv_recent_comments(10, 6, '<li>', '</li>', true, 0); } ?></ul>
  </div>
  <div class="MostCommented">
   <h3>Most Commented</h3>
   <ul><?php if (function_exists('mdv_most_commented')) {  mdv_most_commented(); } ?></ul>
  </div>
 </div>
</div>

<div class="FooterBG">
<div class="Footer">
<p><?php bloginfo('name'); ?> is proudly powered by WordPress - <a href="http://bloggingpro.com/">BloggingPro</a> theme by: <a href="http://designdisease.com">Design Disease</a></p>

</div>
</div>

</div>

</body>
</html>
