

<section class="suche">
<h6 class="unsichtbar">Suchformular</h6>
  <form action="<?php echo home_url( '/' ); ?>" method="get" class="searchform">

  	<input type="text" name="s" id="search" value="<?php the_search_query(); ?>" placeholder="Suchbegriff eingeben ..." />
  	<input name="submitsearch" type="submit" value="Suchen">

  </form>
</section>