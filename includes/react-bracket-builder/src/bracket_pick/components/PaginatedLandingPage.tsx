import React, { useState } from 'react';

import { useDomContentLoaded } from '../../utils/hooks';
import { ActionButton } from './shared/ActionButton';

export const PaginatedLandingPage = () => {
	const [bracketScale, setBracketScale] = useState(1);
	const domContentLoaded = useDomContentLoaded();

	return (
		<div className={`wpbb-paginated-landing-page wpbb-dark-mode`}>
			<div className={'wpbb-slogan-container'}>
				<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
			</div>
			<div className='wpbb-bracket-image-container'>
				<img src='https://wpbb-bracket-images.s3.amazonaws.com/bracket-m7g1t-dark-center-cropped.png'></img>
			</div>
			<ActionButton label='START' onClick={() => { }} />
		</div>
	)
}