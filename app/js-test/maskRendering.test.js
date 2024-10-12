const assert = require('assert');
const { include, loadJSON } = require('./helpers/helpers');
include('app/js/maskRendering');

describe('Mask rendering', () => {
	const sampleData = loadJSON('app/js-test/testdata/masks-sample');
	
	it('should render default public mask', () => {
		const result = maskRendering.renderMask(sampleData[0]);
		assert.equal(result, '<span class="mask" data-mask="0.0"><i data-icon="eye" class="global"></i>Public</span>');
	});
	it('should render default personal mask', () => {
		const result = maskRendering.renderMask(sampleData[1]);
		assert.equal(result, '<span class="mask" data-mask="96191857.1"><i data-icon="user" class="personal"></i>Private</span>');
	});
	it('should render default corp mask', () => {
		const result = maskRendering.renderMask(sampleData[2]);
		assert.equal(result, '<span class="mask" data-mask="98363074.2"><i data-icon="star" class="corporate"></i>Corp</span>');
	});	
	it('should render default alliance mask', () => {
		const result = maskRendering.renderMask(sampleData[3]);
		assert.equal(result, '<span class="mask" data-mask="99005476.3"><i data-icon="star" class="alliance"></i>Alliance</span>');
	});
	it('should render owned personal mask', () => {
		const result = maskRendering.renderMask(sampleData[4]);
		assert.equal(result, '<span class="mask" data-mask="1.0"><i data-icon="user" class="personal"></i>Owned</span>');
	});
});