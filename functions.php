<?php
if ( !function_exists( 'sportspress_event_details' ) ) {
	function sportspress_event_details( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$date = get_the_time( get_option('date_format'), $id );
		$time = get_the_time( get_option('time_format'), $id );
		$leagues = get_the_terms( $id, 'sp_league' );
		$seasons = get_the_terms( $id, 'sp_season' );

		$data = array( __( 'Date', 'sportspress' ) => $date, __( 'Time', 'sportspress' ) => $time );

		if ( $leagues )
			$data[ __( 'League', 'sportspress' ) ] = sportspress_array_value( $leagues, 0, '—' )->name;

		if ( $seasons )
			$data[ __( 'Season', 'sportspress' ) ] = sportspress_array_value( $seasons, 0, '—' )->name;


		$output = '<h3>' . __( 'Details', 'sportspress' ) . '</h3>';

		$output .= '<table class="sp-event-details sp-data-table"><tbody>';
		
		$i = 0;

		foreach( $data as $label => $value ):

			$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';
			$output .= '<th>' . $label . '</th>';
			$output .= '<td>' . $value . '</td>';
			$output .= '</tr>';

			$i++;

		endforeach;

		$output .= '</tbody></table>';

		return $output;

	}
}

if ( !function_exists( 'sportspress_event_results' ) ) {
	function sportspress_event_results( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$teams = (array)get_post_meta( $id, 'sp_team', false );
		$results = sportspress_array_combine( $teams, (array)get_post_meta( $id, 'sp_results', true ) );
		$result_labels = sportspress_get_var_labels( 'sp_result' );

		$output = '';

		// Initialize and check
		$table_rows = '';

		$i = 0;

		foreach( $results as $team_id => $result ):
			if ( sportspress_array_value( $result, 'outcome', '-1' ) != '-1' ):

				unset( $result['outcome'] );

				$table_rows .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

				$table_rows .= '<td class="column_name">' . get_the_title( $team_id ) . '</td>';

				foreach( $result_labels as $key => $label ):
					if ( $key == 'name' )
						continue;
					if ( array_key_exists( $key, $result ) && $result[ $key ] != '' ):
						$value = $result[ $key ];
					else:
						$value = '—';
					endif;
					$table_rows .= '<td class="data-' . $key . '">' . $value . '</td>';
				endforeach;

				$table_rows .= '</tr>';

				$i++;

			endif;
		endforeach;

		if ( ! empty( $table_rows ) ):

			$output .= '<h3>' . __( 'Results', 'sportspress' ) . '</h3>';

			$output .= '<table class="sp-event-results sp-data-table"><thead>';
			$output .= '<th class="data-name">' . __( 'Team', 'sportspress' ) . '</th>';
			foreach( $result_labels as $key => $label ):
				$output .= '<th class="data-' . $key . '">' . $label . '</th>';
			endforeach;
			$output .= '</tr>' . '</thead>' . '<tbody>';
			$output .= $table_rows;
			$output .= '</tbody></table>';

		endif;

		return $output;

	}
}

if ( !function_exists( 'sportspress_event_players' ) ) {
	function sportspress_event_players( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$teams = (array)get_post_meta( $id, 'sp_team', false );
		$staff = (array)get_post_meta( $id, 'sp_staff', false );
		$stats = (array)get_post_meta( $id, 'sp_players', true );
		$statistic_labels = sportspress_get_var_labels( 'sp_statistic' );

		$output = '';

		foreach( $teams as $key => $team_id ):
			if ( ! $team_id ) continue;

			// Get results for players in the team
			$players = sportspress_array_between( (array)get_post_meta( $id, 'sp_player', false ), 0, $key );
			$data = sportspress_array_combine( $players, sportspress_array_value( $stats, $team_id, array() ) );

			$output .= '<h3>' . get_the_title( $team_id ) . '</h3>';

			$output .= '<table class="sp-event-statistics sp-data-table">' . '<thead>' . '<tr>';

			$output .= '<th class="data-number">#</th>';
			$output .= '<th class="data-number">' . __( 'Player', 'sportspress' ) . '</th>';

			foreach( $statistic_labels as $key => $label ):
				$output .= '<th class="data-' . $key . '">' . $label . '</th>';
			endforeach;

			$output .= '</tr>' . '</thead>' . '<tbody>';

			$i = 0;

			foreach( $data as $player_id => $row ):

				if ( ! $player_id )
					continue;

				$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

				$number = get_post_meta( $player_id, 'sp_number', true );

				// Player number
				$output .= '<td class="data-number">' . $number . '</td>';

				// Name as link
				$permalink = get_post_permalink( $player_id );
				$name = get_the_title( $player_id );
				$output .= '<td class="data-name">' . '<a href="' . $permalink . '">' . $name . '</a></td>';

				foreach( $statistic_labels as $key => $label ):
					if ( $key == 'name' )
						continue;
					if ( array_key_exists( $key, $row ) && $row[ $key ] != '' ):
						$value = $row[ $key ];
					else:
						$value = 0;
					endif;
					$output .= '<td class="data-' . $key . '">' . $value . '</td>';
				endforeach;

				$output .= '</tr>';

				$i++;

			endforeach;

			$output .= '</tbody>';

			if ( array_key_exists( 0, $data ) ):

				$output .= '<tfoot><tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

				$number = get_post_meta( $player_id, 'sp_number', true );

				// Player number
				$output .= '<td class="data-number">&nbsp;</td>';
				$output .= '<td class="data-name">' . __( 'Total', 'sportspress' ) . '</td>';

				$row = $data[0];

				foreach( $statistic_labels as $key => $label ):
					if ( $key == 'name' ):
						continue;
					endif;
					$output .= '<td class="data-' . $key . '">' . sportspress_array_value( $row, $key, '—' ) . '</td>';
				endforeach;

				$output .= '</tr></tfoot>';

			endif;

			$output .= '</table>';

		endforeach;

		return $output;

	}
}


if ( !function_exists( 'sportspress_event_staff' ) ) {
	function sportspress_event_staff( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$staff = (array)get_post_meta( $id, 'sp_staff', false );

		$output = '';

		return $output;

	}
}


if ( !function_exists( 'sportspress_event_venue' ) ) {
	function sportspress_event_venue( $id ) {

		$venues = get_the_terms( $id, 'sp_venue' );

		$output = '';

		if ( ! $venues )
			return $output;

		foreach( $venues as $venue ):

			$t_id = $venue->term_id;
			$term_meta = get_option( "taxonomy_$t_id" );

			$address = sportspress_array_value( $term_meta, 'sp_address', '' );
			$latitude = sportspress_array_value( $term_meta, 'sp_latitude', 0 );
			$longitude = sportspress_array_value( $term_meta, 'sp_longitude', 0 );
		
			$output .= '<h3>' . $venue->name . '</h3>';
			$output .= '<div class="sp-google-map" data-address="' . $address . '" data-latitude="' . $latitude . '" data-longitude="' . $longitude . '"></div>';

		endforeach;

		return $output;

	}
}

if ( !function_exists( 'sportspress_league_table' ) ) {
	function sportspress_league_table( $id = null, $args = '' ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$defaults = array(
			'number_label' => __( 'Pos', 'sportspress' ),
			'thumbnails' => 1,
			'thumbnail_size' => 'thumbnail'
		);

		$r = wp_parse_args( $args, $defaults );

		$data = sportspress_get_league_table_data( $id );

		$output = '<table class="sp-league-table sp-data-table">' . '<thead>' . '<tr>';

		// The first row should be column labels
		$labels = $data[0];

		// Remove the first row to leave us with the actual data
		unset( $data[0] );

		$output .= '<th class="data-number">' . $r['number_label'] . '</th>';
		foreach( $labels as $key => $label ):
			$output .= '<th class="data-' . $key . '">' . $label . '</th>';
		endforeach;

		$output .= '</tr>' . '</thead>' . '<tbody>';

		$i = 0;

		foreach( $data as $team_id => $row ):

			$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

			// Position as number
			$output .= '<td class="data-number">' . $i . '</td>';

			// Thumbnail and name as link
			$permalink = get_post_permalink( $team_id );
			if ( $r['thumbnails'] ):
				$thumbnail = get_the_post_thumbnail( $team_id, $r['thumbnail_size'], array( 'class' => 'logo' ) );
			else:
				$thumbnail = null;
			endif;
			$name = sportspress_array_value( $row, 'name', sportspress_array_value( $row, 'name', '&nbsp;' ) );
			$output .= '<td class="data-name">' . ( $thumbnail ? $thumbnail . ' ' : '' ) . '<a href="' . $permalink . '">' . $name . '</a></td>';

			foreach( $labels as $key => $value ):
				if ( $key == 'name' )
					continue;
				$output .= '<td class="data-' . $key . '">' . sportspress_array_value( $row, $key, '—' ) . '</td>';
			endforeach;

			$output .= '</tr>';

			$i++;

		endforeach;

		$output .= '</tbody>' . '</table>';

		return $output;

	}
}


if ( !function_exists( 'sportspress_team_columns' ) ) {
	function sportspress_team_columns( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$leagues = get_the_terms( $id, 'sp_league' );

		$output = '';

		// Loop through data for each league
		foreach ( $leagues as $league ):

			$data = sportspress_get_team_columns_data( $id, $league->term_id );

			if ( sizeof( $data ) <= 1 )
				continue;

			if ( sizeof( $leagues ) > 1 )
				$output .= '<h4 class="sp-team-league-name">' . $league->name . '</h4>';

			// The first row should be column labels
			$labels = $data[0];

			// Remove the first row to leave us with the actual data
			unset( $data[0] );

			$output .= '<table class="sp-team-columns sp-data-table">' . '<thead>' . '<tr>';

			foreach( $labels as $key => $label ):
				$output .= '<th class="data-' . $key . '">' . $label . '</th>';
			endforeach;

			$output .= '</tr>' . '</thead>' . '<tbody>';

			$i = 0;

			foreach( $data as $season_id => $row ):

				$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

				foreach( $labels as $key => $value ):
					$output .= '<td class="data-' . $key . '">' . sportspress_array_value( $row, $key, '—' ) . '</td>';
				endforeach;

				$output .= '</tr>';

				$i++;

			endforeach;

			$output .= '</tbody>' . '</table>';


		endforeach;

		return $output;

	}
}

if ( !function_exists( 'sportspress_player_list' ) ) {
	function sportspress_player_list( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$data = sportspress_get_player_list_data( $id );

		$output = '<table class="sp-player-list sp-data-table">' . '<thead>' . '<tr>';

		// The first row should be column labels
		$labels = $data[0];

		// Remove the first row to leave us with the actual data
		unset( $data[0] );

		$output .= '<th class="data-number">#</th>';
		foreach( $labels as $key => $label ):
			$output .= '<th class="data-' . $key . '">' . $label . '</th>';
		endforeach;

		$output .= '</tr>' . '</thead>' . '<tbody>';

		$i = 0;

		foreach( $data as $player_id => $row ):

			$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

			// Player number
			$number = get_post_meta( $player_id, 'sp_number', true );
			$output .= '<td class="data-number">' . ( $number ? $number : '&nbsp;' ) . '</td>';

			// Name as link
			$permalink = get_post_permalink( $player_id );
			$name = sportspress_array_value( $row, 'name', sportspress_array_value( $row, 'name', '&nbsp;' ) );
			$output .= '<td class="data-name">' . '<a href="' . $permalink . '">' . $name . '</a></td>';

			foreach( $labels as $key => $value ):
				if ( $key == 'name' )
					continue;
				$output .= '<td class="data-' . $key . '">' . sportspress_array_value( $row, $key, '—' ) . '</td>';
			endforeach;

			$output .= '</tr>';

			$i++;

		endforeach;

		$output .= '</tbody>' . '</table>';

		return $output;

	}
}

if ( !function_exists( 'sportspress_player_metrics' ) ) {
	function sportspress_player_metrics( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		global $sportspress_countries;

		$number = get_post_meta( $id, 'sp_number', true );
		$nationality = get_post_meta( $id, 'sp_nationality', true );
		$metrics = sportspress_get_player_metrics_data( $id );

		$flag_image = '<img src="' . SPORTSPRESS_PLUGIN_URL . 'assets/images/flags/' . strtolower( $nationality ) . '.png" class="sp-flag">';

		$common = array(
			__( 'Number', 'sportspress' ) => $number,
			__( 'Nationality', 'sportspress' ) => $flag_image . ' ' . sportspress_array_value( $sportspress_countries, $nationality, '—' ),
		);

		$data = array_merge( $common, $metrics );

		$output = '<table class="sp-player-metrics sp-data-table">' . '<tbody>';

		$i = 0;

		foreach( $data as $label => $value ):

			$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '"><th>' . $label . '</th><td>' . $value . '</td></tr>';

			$i++;

		endforeach;

		$output .= '</tbody>' . '</table>';


		return $output;

	}
}

if ( !function_exists( 'sportspress_player_league_statistics' ) ) {
	function sportspress_player_league_statistics( $league_id, $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$data = sportspress_get_player_statistics_data( $id, $league_id );

		// The first row should be column labels
		$labels = $data[0];

		// Remove the first row to leave us with the actual data
		unset( $data[0] );

		$output = '<table class="sp-player-statistics sp-data-table">' . '<thead>' . '<tr>';

		foreach( $labels as $key => $label ):
			$output .= '<th class="data-' . $key . '">' . $label . '</th>';
		endforeach;

		$output .= '</tr>' . '</thead>' . '<tbody>';

		$i = 0;

		foreach( $data as $season_id => $row ):

			$output .= '<tr class="' . ( $i % 2 == 0 ? 'odd' : 'even' ) . '">';

			foreach( $labels as $key => $value ):
				$output .= '<td class="data-' . $key . '">' . sportspress_array_value( $row, $key, '—' ) . '</td>';
			endforeach;

			$output .= '</tr>';

			$i++;

		endforeach;

		$output .= '</tbody>' . '</table>';

		return $output;
	}
}

if ( !function_exists( 'sportspress_player_statistics' ) ) {
	function sportspress_player_statistics( $id = null ) {

		if ( ! $id ):
			global $post;
			$id = $post->ID;
		endif;

		$leagues = get_the_terms( $id, 'sp_league' );

		$output = '';

		// Loop through statistics for each league
		foreach ( $leagues as $league ):

			if ( sizeof( $leagues ) > 1 )
				$output .= '<h4 class="sp-table-name sp-player-league-name">' . $league->name . '</h4>';

			$output .= sportspress_player_league_statistics( $league->term_id, $id );

		endforeach;

		return $output;

	}
}
