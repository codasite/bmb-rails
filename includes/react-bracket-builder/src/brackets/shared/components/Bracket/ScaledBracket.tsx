import React from 'react';
import { BracketProps, ScaledBracketProps } from '../types';

export const ScaledBracket = (props: ScaledBracketProps) => {
	const {
		BracketComponent,
		getBracketHeight = () => 350,
		matchTree,
		scale = .3
	} = props

	delete props.BracketComponent

	const height = getBracketHeight(matchTree.rounds.length)
	console.log('ScaledBracket', height, scale)

	return (
		<div className={`tw-flex tw-flex-col tw-justify-center tw-items-center tw-h-[${height}px]`}>
			<div className={`tw-scale-${scale * 100}`}>
				<BracketComponent {...props} />
			</div>
		</div>
	)
}