import React, { useState } from 'react';
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../../shared/models/MatchTree';
import { ActionButton } from '../../../shared/components/ActionButtons';
import { PickableBracket } from '../../../shared/components/Bracket';
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket';

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
				{matchTree &&
					<ScaledBracket
						BracketComponent={PickableBracket}
						matchTree={matchTree}
					/>
				}
				<ActionButton
					darkMode={true}
					variant='small-green'
					onClick={onStart}
				>Start</ActionButton>
			</div>
		</div>
	)
}