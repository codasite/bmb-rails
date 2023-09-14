import React, { } from 'react';
//@ts-ignore
import { MatchColumnProps } from '../types';
//@ts-ignore
import { DefaultMatchBox } from '../MatchBox/DefaultMatchBox';
import { FlexMatchBox } from '../MatchBox';
import { FlexTeamSlot } from '../TeamSlot';


export const FlexMatchColumn = (props: MatchColumnProps) => {
	const {
		matches,
		matchPosition,
		matchTree,
		setMatchTree,
		MatchBoxComponent = FlexMatchBox,
		matchGap,
		teamGap,
		teamHeight,
		onTeamClick,
	} = props


	return (
		<div className={`tw-flex tw-flex-col tw-justify-center tw-flex-grow tw-gap-${matchGap}`}>
			{matches.map((match, index) => {
				return (
					<MatchBoxComponent
						key={index}
						match={match}
						matchPosition={matchPosition}
						matchTree={matchTree}
						setMatchTree={setMatchTree}
						teamGap={teamGap}
						teamHeight={teamHeight}
						onTeamClick={onTeamClick}
					/>
				)
			})}
		</div>
	)
}