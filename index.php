<?php

// Debugging
error_reporting(E_ALL);

// Get variable junk
$page = isset($_GET["page"]) ? $_GET["page"] : 'stocked';
$stocked = 1;

if($page == 'unstocked'){
	$stocked = 0;
}

// Mysql stuff
mysql_pconnect( 'localhost', 'ckcollab_food', 'FTQ7P3LwxshbmatH'  );
mysql_select_db('ckcollab_food');

$result = mysql_query("SELECT category FROM item WHERE stocked = ". $stocked);
$categories = array();

while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	$name = $row[0];
    if(!in_array($name, $categories)) {
		array_push($categories, $name);
	}
}

$result = mysql_query("SELECT id, name, category, is_essential, perishes_in_days, date_modified FROM item WHERE stocked = ". $stocked);
$items = array();

while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    array_push($items, array(
		'name' => $row[1],
		'category' => $row[2],
		'is_essential' => $row[3],
		'perishes_in_days' => $row[4],
		'date_modified' => $row[5]
	));
}

$recipes = array();

if($page == 'recipe'){
	$result = mysql_query(
		"SELECT 
			recipe.name,
			item.name
			
		FROM recipe
		JOIN recipe_relation ON recipe.id = recipe_relation.recipe_id
		JOIN item ON item.id = recipe_relation.item_id"
	);
	
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if(!array_key_exists($row[0], $recipes)){
			$recipes[$row[0]] = array();
		}

		array_push($recipes[$row[0]], $row[1]);
	}
	
	//echo '<pre>';
	//echo var_dump($recipes);
	//echo '</pre>';
}

mysql_free_result($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sexy Shopping List</title>
    <meta
     name="viewport"
     content="width=100%; 
             initial-scale=1;
             maximum-scale=1;
             minimum-scale=1; 
             user-scalable=no;"
    />
    <meta name="description" content="">
    <meta name="author" content="">
	
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="img/favico.png">
	
	<script>
		$(document).ready(function(){
			$('.food-item').click(function(){
				var foodItem = this;
				var foodName = $(foodItem).children('.item_name').html().replace(/(^\s+|\s+$)/g, '');
			
				// Toggle from stocked to unstocked
				var stocked = <?php echo $stocked; ?> == 1 ? 0 : 1;
			
				$.get('manage.php?item_name=' + foodName + '&stocked=' + stocked);
				
				// Create new undo-warning
				var undo = $('#undo_action_template').last().clone();
				
				
				// For some reason i have to strip spaces from end of this
				$(undo).children('.item_name').html(foodName);
				
				$(foodItem).after(undo);
				
				$(undo).fadeIn('slow', function(){
					$(foodItem).fadeOut('slow', function(){
						$(foodItem).hide();
					});
				});
				
				$(undo).click(function(){
					// Toggle from stocked to unstocked
					stocked = stocked == 1 ? 0 : 1;
				
					$.get('manage.php?item_name=' + foodName + '&stocked=' + stocked);
				
					$(foodItem).fadeIn(function(){
						$(undo).fadeOut('slow');
					});
				});
				
				
				// Count how many food items are in each category, if last item remove category
				$('.category').each(function(n, item){
					var count = $(item).children('.food-items').children('.food-item').length;
					
					if(count==0) {
						$(item).fadeOut('slow').remove();
					}
				});
			});
			
			// Make footer stick to bottom fix
			$(window).scroll(function(){
				var height = $(window).height();
				var scrollPos = $(window).scrollTop(); 
				
				//$('.navbar-fixed-bottom').css({ 'top': height + scrollPos - 100 });
			});
		});
	</script>
</head>

<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand" href="#"><img src="img/logo.png"> The Sexy Shopping List</a>
            </div>
        </div>
    </div>



	<?php if($page == 'stocked' || $page == 'unstocked'): ?>
		<div class="container">
			<?php foreach($categories as $category): ?>
				<div class="category">
					<div class="food-header">
						<button class="btn btn-large btn-block btn-primary disabled" type="button"><?php echo $category; ?></button>
					</div>
					
					<div class="food-items">
						<?php foreach($items as $item): ?>
							<?php if($item["category"] == $category): ?>
								<button class="btn btn-large btn-block food-item" type="button">
									<?php if($item["is_essential"]): ?>
										<img src="img/star.png">
									<?php endif; ?>
								
									<span class="item_name"><?php echo $item["name"]; ?></span>
									
									<?php 
						
										if($stocked == 1 and $item["perishes_in_days"] > 0) {
											// Take current day, add two days
											$worry_about_expiring = date("Y-m-d H:i:s", strtotime("+2 day"));
											// Take time item was stocked, add days until it perishes
											$expires = $date = date("Y-m-d H:i:s",strtotime(str_replace('/','-',$item["date_modified"]) . ' +'.$item["perishes_in_days"].' days'));

											// If it expires within the 2 days, warn
											if($expires < $worry_about_expiring) {
												echo '<img src="img/expired.png">';
											}
										}
									?>
								</button>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	
	<?php if($page == 'recipe'): ?>
		<div class="container">
			<?php foreach($recipes as $recipe_name => $recipe_item): ?>
				<button class="btn btn-large btn-block recipe" type="button">
					<span class="item_name"><?php echo $recipe_name; ?></span>
				</button>
				
				<?php
					$numItems = count($recipe_item);
					$i = 0;
				?>
				
				<?php foreach($recipe_item as $food_item): ?>
					<span class="item_name">
					    <?php if(++$i<$numItems): ?> 
							<?php echo $food_item.','; ?>
						<?php else: ?>
							<?php echo $food_item; ?>
						<?php endif; ?>
					</span>
					

				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	
	<button id="undo_action_template" class="alert btn-block" style="display: none;">
		<strong>Just in Timber-case!</strong> Best check yo self, undo action on <span class="item_name"></span>?
	</button>
	
	<div class="navbar navbar-inverse navbar-fixed-bottom">
		<div class="navbar-inner">
			<div class="container">
				<div class="btn-toolbar">
					<div class="btn-group">
						<a class="btn btn-large <?php if($page == 'stocked'){echo 'btn-primary';} ?>" href="?page=stocked">Cook</a>
						<a class="btn btn-large <?php if($page == 'unstocked'){echo 'btn-primary';} ?>" href="?page=unstocked">Buy</a>
						<a class="btn btn-large <?php if($page == 'recipe'){echo 'btn-primary';} ?>" href="?page=recipe">Recipe</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
