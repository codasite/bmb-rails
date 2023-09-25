import React, { useState, useContext } from 'react';
import { BracketProps } from '../types';
import {
	getFirstRoundMatchGap as getDefaultFirstRoundMatchGap,
	getMatchGap,
	getSubsequentMatchGap as getDefaultSubsequentMatchGap,
	getBracketHeight as getDefaultBracketHeight,
	getBracketWidth as getDefaultBracketWidth,
	getTeamGap as getDefaultTeamGap,
	getTeamHeight as getDefaultTeamHeight,
	getTeamFontSize as getDefaultTeamFontSize,
	getTeamWidth,
} from '../../utils'
import { DefaultMatchColumn } from '../MatchColumn';
import { DefaultTeamSlot } from '../TeamSlot';
import { BracketLines } from './BracketLines';
import { DarkModeContext } from '../../context';
import { ActionButton } from '../ActionButtons';


export const PaginatedDefaultBracket = (props: BracketProps) => {
	const {
		getBracketHeight = getDefaultBracketHeight,
		getBracketWidth = () => 260,
		getTeamHeight = () => getDefaultTeamHeight(4),
		getTeamGap = () => getDefaultTeamGap(0),
		getFirstRoundMatchGap = () => getDefaultFirstRoundMatchGap(5),
		getSubsequentMatchGap = getDefaultSubsequentMatchGap,
		getTeamFontSize = () => getDefaultTeamFontSize(4),
		matchTree,
		setMatchTree,
		MatchColumnComponent = DefaultMatchColumn,
		MatchBoxComponent,
		TeamSlotComponent = DefaultTeamSlot,
		MatchBoxChildComponent,
		onTeamClick,
		lineStyle,
	} = props

	const [page, setPage] = useState(0)

	const darkMode = useContext(DarkModeContext);

	const linesStyle = lineStyle || {
		className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
	}

	const roundIndex = 0

	if (roundIndex === matchTree.rounds.length - 1) {
		// last round, handle differently
		return null
	}

	let matches1 = matchTree.rounds[roundIndex].matches
	let matches2 = matchTree.rounds[roundIndex + 1].matches

	const leftSide = true

	if (leftSide) {
		// if left side, get first half of matches
		matches1 = matches1.slice(0, matches1.length / 2)
		matches2 = matches2.slice(0, matches2.length / 2)
	} else {
		// if right side, get second half of matches
		matches1 = matches1.slice(matches1.length / 2)
		matches2 = matches2.slice(matches2.length / 2)
	}

	const numRounds = matchTree.rounds.length
	const depth = numRounds - roundIndex - 1
	const matchGap1 = getFirstRoundMatchGap(numRounds)
	const teamGap = getTeamGap(depth)
	const teamHeight = getTeamHeight(numRounds)
	const teamWidth = getTeamWidth(numRounds)
	const teamFontSize = getTeamFontSize(numRounds)

	const matchHeight = teamHeight * 2 + teamGap
	const matchGap2 = getSubsequentMatchGap(matchHeight, matchGap1, matchHeight)

	const matchCol1 =
		<MatchColumnComponent
			matches={matches1}
			matchPosition={leftSide ? 'left' : 'right'}
			matchTree={matchTree}
			setMatchTree={setMatchTree}
			MatchBoxComponent={MatchBoxComponent}
			TeamSlotComponent={TeamSlotComponent}
			matchGap={matchGap1}
			teamGap={teamGap}
			teamHeight={teamHeight}
			teamWidth={teamWidth}
			teamFontSize={teamFontSize}
			onTeamClick={onTeamClick}
		/>

	const matchCol2 =
		<MatchColumnComponent
			matches={matches2}
			matchPosition={leftSide ? 'left' : 'right'}
			matchTree={matchTree}
			MatchBoxComponent={MatchBoxComponent}
			TeamSlotComponent={TeamSlotComponent}
			matchGap={matchGap2}
			teamGap={teamGap}
			teamHeight={teamHeight}
			teamWidth={teamWidth}
			teamFontSize={teamFontSize}
		/>

	const maxW = getBracketWidth(numRounds)

	return (
		<div className={`tw-flex tw-flex-col tw-gap-48 tw-min-h-screen tw-w-[${maxW}px] tw-m-auto tw-py-60`}>
			<div className='tw-flex tw-justify-center'>
				<h2 className='tw-text-24 tw-font-700 tw-text-white'>{`Round ${roundIndex + 1}`}</h2>
			</div>
			<div className={`tw-flex tw-justify-between tw-flex-grow`}>
				{leftSide ? matchCol1 : matchCol2}
				{leftSide ? matchCol2 : matchCol1}
				<BracketLines
					rounds={matchTree.rounds}
					style={linesStyle}
				/>
			</div>
			<ActionButton variant='white'>Next</ActionButton>
		</div>
	)
}