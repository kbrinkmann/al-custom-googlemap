function CustomGoogleMap() {
	

	window.onload = jQuery.proxy(this.bindGM, this);
	
	this.setting = JSON.parse( jQuery("#metabox-cgm input#cgm-configmap").val() );

	jQuery('#metabox-cgm #btnSearchAddress').on('click', jQuery.proxy(this.searchAddress, this));
	jQuery('#metabox-cgm #cgm-addressfield').on('keydown', function(e) { if(e.which == 13) e.preventDefault(); });
}

CustomGoogleMap.prototype = {
		
	/**
	 *@desc bind google map asynchronously to admin page 
	 */
	bindGM	: function() {
	
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "http://maps.googleapis.com/maps/api/js?key=" + CustomGoogleMapVar.key + "&sensor=false&callback=setupMap";
		document.body.appendChild(script);
	
	},
	
	/**
	 *@desc setup location, zoom and marker for the map 
	 */
	setupMap	: function() {
	
			
		this.loc = new google.maps.LatLng( this.setting['marker']['lat'], this.setting['marker']['lng']);
		this.maploc = new google.maps.LatLng( this.setting['map']['center']['lat'], this.setting['map']['center']['lng']);
		
		
		var mapOptions = {
				    zoom: this.setting['map']['zoom'],
				    center: this.maploc,
				    mapTypeId: eval('google.maps.MapTypeId.' + this.setting['map']['maptype'])
		};
		 
		this.map = new google.maps.Map(document.getElementById("custom-google-map"), mapOptions);
		
		this.marker = new google.maps.Marker({
			map: this.map,
			position: this.loc,
			draggable: true,
			streetViewControl: false,
			mapTypeControl: true
		});
	
		google.maps.event.addListener(this.map, 'maptypeid_changed', jQuery.proxy(this.onMaptypeChanged, this) );
		google.maps.event.addListener(this.map, 'center_changed', jQuery.proxy(this.onMapCenterChanged, this) );
		google.maps.event.addListener(this.map, 'zoom_changed', jQuery.proxy(this.onZoomLevelChanged, this) );
		google.maps.event.addListener(this.map, 'click', jQuery.proxy(this.onSetMarker, this) );
		google.maps.event.addListener(this.marker, 'dragend', jQuery.proxy(this.onDragendMarker, this) );
		
	},
	
	/**
	 * @desc lookup address with google map geocode and center map and marker to this position 
	 */
	searchAddress	: function(e) {

		e.preventDefault();
		
		geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': jQuery('#metabox-cgm #cgm-addressfield').val() }, jQuery.proxy( function(results, status) {
			
			if (status == google.maps.GeocoderStatus.OK) {
			  
				this.map.setCenter(results[0].geometry.location);
				this.marker.setPosition( results[0].geometry.location );
			
			} else {
			  alert("Geocode was not successful for the following reason: " + status);
			}
		}, this) );
		
	},

	
	onSetMarker	: function(e) {
	
		this.marker.setPosition( e.latLng );
		this.onDragendMarker();
	},
	
	onMapCenterChanged	: function() {
		
		this.setting['map']['center']['lat'] = this.map.getCenter().lat();
		this.setting['map']['center']['lng'] = this.map.getCenter().lng();
		jQuery("#metabox-cgm input#cgm-configmap").val( JSON.stringify( this.setting ) );	//save current settings in hidden field
	},

	
	onMaptypeChanged	: function() {
		
		this.setting['map']['maptype'] = String(this.map.getMapTypeId()).toUpperCase();
		jQuery("#metabox-cgm input#cgm-configmap").val( JSON.stringify( this.setting ) );	//save current settings in hidden field
	},
	
	
	onZoomLevelChanged	: function() {
		
		this.map.setCenter( this.marker.getPosition() );	//correct map to center of marker
		this.setting['map']['zoom'] = this.map.getZoom();
		jQuery("#metabox-cgm input#cgm-configmap").val( JSON.stringify( this.setting ) );	//save current settings in hidden field
	},
	
	
	/**
	 * @desc position marker was moved, save new position 
	 */
	onDragendMarker	: function() {
		
		pos = this.marker.getPosition();
		this.setting['marker']['lat'] = pos.lat();
		this.setting['marker']['lng'] = pos.lng();
			
		jQuery("#metabox-cgm input#cgm-configmap").val( JSON.stringify( this.setting ) );	//save current settings in hidden field
			
	}
	
	
		
};



var cgm_var;
jQuery(document).ready(function(){

	cgm_var = new CustomGoogleMap();
	
});

function setupMap() {
	cgm_var.setupMap();
}
