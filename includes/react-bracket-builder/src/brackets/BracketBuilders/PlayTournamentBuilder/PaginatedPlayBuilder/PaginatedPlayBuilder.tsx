import React, { useState } from 'react';
import { PlayBuilderProps } from '../types';
import { PaginatedPickableBracket } from '../../../shared/components/Bracket';
import { LandingPage } from './LandingPage';


export const PaginatedPlayBuilder = (props: PlayBuilderProps) => {
	const {
		matchTree,
		setMatchTree,
	} = props

	return (
		<LandingPage
			onStart={() => { }}
		/>
		// <div className={`wpbb-reset tw-uppercase tw-bg-dd-blue tw-dark`}>
		// 	{matchTree &&
		// 		<PaginatedPickableBracket
		// 			matchTree={matchTree}
		// 			setMatchTree={setMatchTree}
		// 		/>
		// 	}
		// </div>
	)
}