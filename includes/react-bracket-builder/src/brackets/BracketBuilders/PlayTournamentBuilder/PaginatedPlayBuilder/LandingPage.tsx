import React, { useState } from 'react';
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../../shared/models/MatchTree';
import { ActionButton } from '../../../shared/components/ActionButtons';

interface LandingPageProps {
	onStart: () => void;
	matchTree?: MatchTree;
}

export const LandingPage = (props: LandingPageProps) => {
	const {
		onStart,
		matchTree,
	} = props;


	return (
		<div className={`wpbb-reset tw-flex tw-uppercase tw-min-h-screen tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark `} style={{ 'backgroundImage': `url(${darkBracketBg})` }}>
			<div className='tw-flex tw-flex-col tw-justify-center px-60 tw-max-w-[268px] tw-m-auto'>
				<h1 className='tw-text-center tw-text-48 tw-font-700 tw-w-'>Who You Got?</h1>
				<ActionButton
					height={48}
					borderColor='green'
					borderWidth={4}
					backgroundColor='green/15'
					fontSize={24}
					fontWeight={700}
					onClick={onStart}
					textColor='white'
					borderRadius={8}

				>Start</ActionButton>
				{/* <div className={'wpbb-slogan-container'}>
				<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
			</div>
			<div className='wpbb-bracket-image-container'>
				<img src='https://wpbb-bracket-images.s3.amazonaws.com/bracket-m7g1t-dark-center-cropped.png'></img>
			</div>
			<ActionButton label='START' onClick={onStart} /> */}
			</div>
		</div>
	)
}