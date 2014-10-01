<!--=====================
          Content
======================-->
<section id="content"><div class="ic">More Website Templates @ TemplateMonster.com - August11, 2014!</div>
  <div class="container">
    <div class="row">
      <div class="grid_12">
        <h2 class="ta__center">Recent  Photos</h2>

		<div>
			Total: <?php echo $PM->count; ?>
		</div>

		<div class="taglist">
			<?php
				$tag_block = '';
				foreach ($PM->tag as $tag) {
					$tag_block .= '<span class="tag" val="'.htmlspecialchars($tag['name']).'">'.htmlspecialchars($tag['name']).'</span>';
				}
				echo $tag_block;
			?>
			<br/>
			<input type="button" id="tagfilter" value="apply filter" /> (use Ctrl+click to mark tag as banned)
      <select id="sortby"><option value="like">like</option><option value="date" <?php
        if ($PM->sort == SORT_DATE) echo "selected";
      ?>>date</option></select>
			<script type="text/javascript">
        init_filter()
			</script>
		</div>

		<div id="gallery">
			<?php
				$photo_block = '';
				foreach ($PM->list as $photo) {
					$photo_block .= '<img class="separate" img_id="'.$photo['id'].'" src="'.$photo['src'].'" />';
				}
				echo $photo_block;
			?>
		</div>
      </div>
    </div>

    <div id="pager">
    	<?php echo $PM->pager(); ?>
    	<script type="text/javascript">
    		$('#pager_go').click(function(){ document.location = '?page='+$('#pager_goto').val()})
    	</script>
    </div>

  </div>
  <div class="sep-1"></div>
  <div class="container">
    <div class="row">
      <div class="grid_8">
        <h3>Bio</h3>
        <img src="images/page1_img1.jpg" alt="" class="img_inner fleft noresize">
        <div class="extra_wrapper"><p class="offset__1">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In mollis erat mattis neque facilisis, sit amet ultricies erat rutrum. Cras facilisis, nulla vel viverra auctor, leo magna sodales felis, quis malesuada nibh odio ut velit. Proin pharetra luctus diam, a scelerisque eros convallis accumsan. Maecenas vehicula egestas  derto venenatis. Duis massa elit, auctor non pellentesque vel, aliquet sit amet erat.</p></div>
        <div class="clear"></div>
        <p>Find detailed information about the <a href=" http://blog.templatemonster.com/free-website-templates/" rel="nofollow" class="color1"><strong>freebie</strong></a> here. </p>
        <p>Visit TemplateMonster.com to find more <a href="http://www.templatemonster.com/properties/topic/design-photography/" rel="nofollow" class="color1"><strong>goodies</strong></a> of this kind.</p>
        Proin pharetra luctus diam, a scelerisque eros convallis accumsan. Maecenas vehicula egestas venenatis. <br>
        <a href="#" class="btn">more</a> 
      </div>
      <div class="grid_4">
        <h3>Follow me</h3>
        <ul class="socials">
          <li>
            <div class="fa fa-facebook"></div>
            <a href="#">Be a fan on Facebook</a>
          </li>
          <li>
            <div class="fa fa-twitter"></div>
            <a href="#">Follow me on Twitter</a>
          </li>
          <li>
            <div class="fa fa-google-plus"></div>
            <a href="#">Follow me on Google+</a>
          </li>
          <li>
            <div class="fa fa-youtube"></div>
            <a href="#">Follow me on YouTube</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="sep-1"></div>
  <div class="container">
    <div class="row">
      <div class="grid_7">
        <h3 class="head__1">From the Blog</h3>
        <time class="time-1" datetime="2014-01-01">24.07 <br> 2014</time><p class="offset__2">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In mollis erat mattis neque facilisis, sit amet ultricies erat rutrum. Cras facilisis, nulla vel viverra auctor, leo magna sodales felis, quis malesuada nibh odio ut velit. Proin pharetra luctus diam, a scelerisque eros convallis accumsan. Maecenas vehicula egestas  derto venenatis. Duis </p>
        Dorem ipsum dolor sit amet, consectetur adipiscing elit. In mollis erat mattis neque facilisis, sit amet ultricies erat rutrum. Cras facilisis, nulla vel viverra auctor, leo magna sodales felis. <br>
        <a href="#" class="btn">more</a>
      </div>
      <div class="grid_4 preffix_1">
        <h3 class="head__1">Testimonials</h3>
        <blockquote class="bq_1">
          <img src="images/page1_img2.jpg" alt="" class="img_inner fleft noresize">
          <div class="extra_wrapper">
            <div class="bq_title">Lize Jons</div>
          </div>
          <div class="clear"></div>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. In mollis erat mattis neque facilisis, sit amet ultricies erat rutrum. Cras facilisis, nulla vel viverra auctor...
          <br>
          <a href="#" class="btn">more</a>
        </blockquote>
      </div>
    </div>
  </div>
</section>