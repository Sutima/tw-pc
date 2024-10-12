/** Functions for rendering things relating to masks */
const maskRendering = new function() { 
	/** Render a mask, as returned from masks.php */
	this.renderMask = function(mask) {
		const icons = { global: 'eye', personal: 'user', corporate: 'star', alliance: 'star' };

		return '<span class="mask" data-mask="' + mask.mask + '">'
			+ '<i data-icon="' + icons[mask.ownerType] + '" class="' + mask.ownerType + '"></i>'
			+ mask.label
			+ '</span>';
	}
	
	this.updateActive = function(activeMask) {
		document.getElementById('mask').innerHTML = maskRendering.renderMask(activeMask);
	}
};