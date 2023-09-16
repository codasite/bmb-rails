import React, { useContext } from 'react';
import { MatchBoxChildProps } from '../../types';
//@ts-ignore
import { BracketMetaContext } from '../../../context';
import { WinnerContainer } from './WinnerContainer';
import { LogoContainer } from './LogoContainer';


export const DefaultFinalMatchChildren = (props: MatchBoxChildProps) => {
	const {
		matchPosition,
	} = props

	const {
		date: bracketDate,
		title: bracketTitle
	} = useContext(BracketMetaContext)

	return (
		matchPosition === 'center' ?
			<>
				<WinnerContainer
					{...props}
					topText={bracketTitle}
				/>
				<LogoContainer
					{...props}
					bottomText={bracketDate}
				/>
			</>
			: <></>
	)
}