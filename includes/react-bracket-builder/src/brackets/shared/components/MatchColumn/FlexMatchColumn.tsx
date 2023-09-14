import React, { } from 'react';
//@ts-ignore
import { MatchColumnProps } from '../types';
//@ts-ignore
import { DefaultMatchBox } from '../MatchBox/DefaultMatchBox';
import { FlexMatchBox } from '../MatchBox';
import { FlexTeamSlot } from '../TeamSlot';


const FlexMatchGap = (props: any) => {
	return (
		<div className='tw-flex-grow tw-flex-shrink tw-flex-basis-10 tw-max-h-[16px] tw-min-h-[4px]'></div>
	)
}

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
		<div className={`tw-flex tw-flex-col tw-justify-center tw-flex-grow`}>
			{
				matches.reduce((matches, match, index) => {
					if (!match) {
						return matches
					}
					if (index > 0) {
						matches.push(<FlexMatchGap key={index} />)
					}
					matches.push(
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
					return matches
				}, [] as JSX.Element[])
			}
			{/* {matches.map((match, index) => {
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
			})} */}
		</div>
	)
}