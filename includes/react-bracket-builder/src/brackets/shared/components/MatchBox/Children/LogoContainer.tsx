import React, { useContext } from 'react';
import { bracketConstants } from '../../../constants'
import { MatchBoxChildProps } from '../../types';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../../assets/BMB-ICON-CURRENT.svg'
import { BracketMetaContext } from '../../../context';

interface LogoContainerProps extends MatchBoxChildProps {
	topText?: string,
	topTextColor?: string,
	topTextColorDark?: string,
	topTextFontSize?: number,
	bottomText?: string,
	bottomTextColor?: string,
	bottomTextColorDark?: string,
	bottomTextFontSize?: number,
	logoColor?: string,
	logoColorDark?: string,
	bottom?: number[],

}

export const LogoContainer = (props: LogoContainerProps) => {
	const {
		matchTree,
		topText = 'Who You Got?',
		topTextColor = 'dd-blue',
		topTextColorDark = 'white',
		topTextFontSize = 36,
		bottomText = '',
		bottomTextColor = 'dd-blue',
		bottomTextColorDark = 'white',
		bottomTextFontSize = 36,
		logoColor = 'black/25',
		logoColorDark = 'white',
		bottom = bracketConstants.bracketLogoBottom,
		// color = 
	} = props

	const numRounds = matchTree.rounds.length

	return (
		<div className={`tw-absolute tw-flex tw-flex-col tw-gap-20 tw-justify-between tw-items-center tw-left-[50%] tw-translate-x-[-50%] tw-bottom-[${bottom[numRounds]}px] tw-font-700 tw-whitespace-nowrap `}>
			<span className={`tw-text-${topTextFontSize} tw-text-${topTextColor} dark:tw-text-${topTextColorDark}`}>{topText}</span>
			<BracketLogo className={`tw-w-[124px] tw-text-${logoColor} dark:tw-text-${logoColorDark}`} />
			<span className={`tw-text-${bottomTextFontSize} tw-text-${bottomTextColor} dark:tw-text-${bottomTextColorDark}`}>{bottomText}</span>
		</div>
	)

}