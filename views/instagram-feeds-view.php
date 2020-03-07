<?php $credentials = App\Instagram\Feeder::getApiCredentials(); ?>

<div class="section-instagram-feeds">
	<div class="shell">
		<form action="<?php echo admin_url( 'admin.php?page=instagram-feeds' ); ?>" method="post">
			<div class="form-group">		
				<label class="form-check-label" for="InstagramAppID">App ID</label>
				<p>
					<input type="password" class="form-control" name="InstagramAppID" id="InstagramAppID" value="<?php echo $credentials['AppID']; ?>">		
				</p>		
			</div>
			
			<div class="form-group">	
				<label class="form-check-label" for="InstagramAppSecret">App Secret</label>		
				<p>	
					<input type="password" class="form-control" id="InstagramAppSecret" value="<?php echo $credentials['AppSecret']; ?>" name="InstagramAppSecret">
				</p>
			</div>
						
			<button type="submit" class="btn btn-primary">Save</button>
		</form>
		
		<p>
			<a href="<?php echo App\Instagram\Feeder::getAuthorizationLink(); ?>">Pull Instagram Feeds</a>
		</p>
	</div><!-- shell -->
</div><!-- section-instagram-feeds -->