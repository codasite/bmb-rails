import React, { } from 'react';
//@ts-ignore
import { MatchColumnProps } from '../types';
//@ts-ignore
import { DefaultMatchBox } from '../MatchBox/DefaultMatchBox';


export const DefaultMatchColumn = (props: MatchColumnProps) => {
	const {
		matches,
		matchPosition,
		matchTree,
		setMatchTree,
		MatchBoxComponent = DefaultMatchBox,
		MatchBoxChildComponent,
		TeamSlotComponent,
		matchGap,
		teamGap,
		teamHeight,
		teamWidth,
		teamFontSize,
		onTeamClick,
	} = props

	return (
		<div className={`tw-flex tw-flex-col tw-justify-center tw-gap-[${matchGap}px]`}>
			{
				matches.map((match, index) => {
					return (
						<MatchBoxComponent
							key={index}
							match={match}
							matchPosition={matchPosition}
							matchTree={matchTree}
							setMatchTree={setMatchTree}
							TeamSlotComponent={TeamSlotComponent}
							MatchBoxChildComponent={MatchBoxChildComponent}
							teamGap={teamGap}
							teamHeight={teamHeight}
							teamWidth={teamWidth}
							teamFontSize={teamFontSize}
							onTeamClick={onTeamClick}
						/>
					)
				})}
		</div>
	)
}