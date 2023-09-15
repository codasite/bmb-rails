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
		TeamSlotComponent,
		matchGap,
		teamGap = 20,
		teamHeight = 28,
		onTeamClick,
	} = props

	return (
		<div className={`tw-flex tw-flex-col tw-justify-center tw-gap-[${matchGap}px]`}>
			{matches.map((match, index) => {
				return (
					<MatchBoxComponent
						key={index}
						match={match}
						matchPosition={matchPosition}
						matchTree={matchTree}
						setMatchTree={setMatchTree}
						TeamSlotComponent={TeamSlotComponent}
						teamGap={teamGap}
						teamHeight={teamHeight}
						onTeamClick={onTeamClick}
					>
						{match.parent === null &&
							// <FinalMatchChildren
							// 	match={match}
							// 	matchTree={matchTree}
							// 	matchPosition={matchPosition}
							// 	TeamSlotComponent={TeamSlotComponent}
							// />
							<>
								<div className={`tw-flex tw-flex-col tw-gap-16 tw-absolute tw-bottom-[${winnerContainerBottom[numRounds]}px] tw-items-center tw-left-[50%] tw-translate-x-[-50%]`}>
									<span className='tw-text-64 tw-font-700 tw-whitespace-nowrap tw-text-dd-blue dark:tw-text-white'>{bracketTitle}</span>
									<FinalTeamSlot
										match={match}
										matchTree={matchTree}
									/>
								</div>
								{/* <div className='tw-absolute tw-bottom-0 tw-left-[50%] tw-translate-x-[-50%] tw-text-black/20 dark:tw-text-white/20 '> */}
								<div className={`tw-absolute tw-flex tw-flex-col tw-gap-20 tw-justify-between tw-items-center tw-left-[50%] tw-translate-x-[-50%] tw-bottom-[${bracketLogoBottom[numRounds]}px] tw-text-dd-blue dark:tw-text-white tw-text-36 tw-font-700 tw-whitespace-nowrap `}>
									<span>{sloganText}</span>
									<BracketLogo className={'tw-w-[124px] tw-text-black/25 dark:tw-text-white'} />
									<span>{bracketDate}</span>
								</div>
							</>
						}

					</MatchBoxComponent>
				)
			})}
		</div>
	)
}