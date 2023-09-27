import React, { useState, useContext } from 'react';
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../../shared/assets/bracket-bg-light.png'
import { MatchTree } from '../../../shared/models/MatchTree';
import { ActionButton } from '../../../shared/components/ActionButtons';
import { PickableBracket } from '../../../shared/components/Bracket';
import { DarkModeContext } from '../../../shared/context';
import { ThemeSelector } from '../../../shared/components';

interface FullBracketPageProps {
	onApparelClick: () => void;
	matchTree?: MatchTree;
	darkMode?: boolean;
	setDarkMode?: (darkMode: boolean) => void;
}

export const FullBracketPage = (props: FullBracketPageProps) => {
	const {
		onApparelClick,
		matchTree,
		darkMode,
		setDarkMode,
	} = props;


	return (
		<div className={`wpbb-reset tw-flex tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${darkMode ? ' tw-dark' : ''}`} style={{ 'backgroundImage': `url(${darkMode ? darkBracketBg : lightBracketBg})` }}>
			<div className='tw-flex tw-flex-col tw-justify-center px-60 tw-max-w-[268px] tw-m-auto'>
				<ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
				{matchTree &&
					<div className='tw-flex tw-flex-col tw-justify-center tw-items-center tw-pb-60 tw-h-[350px]'>
						<div className='tw-scale-30'>
							<PickableBracket
								matchTree={matchTree}
								lineStyle={{
									className: `!tw-border-t-white !tw-border-t-[.4px]`,
								}}
							/>
						</div>
					</div>
				}
				<ActionButton
					height={48}
					borderColor='green'
					borderWidth={4}
					backgroundColor='green/15'
					fontSize={24}
					fontWeight={700}
					onClick={onApparelClick}
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