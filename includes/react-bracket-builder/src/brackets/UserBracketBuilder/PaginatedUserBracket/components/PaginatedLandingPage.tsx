import React, { useState } from 'react';

import { ActionButton } from '../../../shared/components/ActionButton'

interface PaginatedLandingPageProps {
	onStart: () => void;
}

export const PaginatedLandingPage = (props: PaginatedLandingPageProps) => {
	const {
		onStart,
	} = props;

	return (
		<div className={`wpbb-paginated-landing-page wpbb-dark-mode`}>
			<div className={'wpbb-slogan-container'}>
				<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
			</div>
			<div className='wpbb-bracket-image-container'>
				<img src='https://wpbb-bracket-images.s3.amazonaws.com/bracket-m7g1t-dark-center-cropped.png'></img>
			</div>
			<ActionButton label='START' onClick={onStart} />
		</div>
	)
}