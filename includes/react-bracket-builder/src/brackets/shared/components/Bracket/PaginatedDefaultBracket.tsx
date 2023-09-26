import React, { useState, useContext, useEffect } from 'react';
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
import { WinnerContainer } from '../MatchBox/Children/WinnerContainer';


export const PaginatedDefaultBracket = (props: BracketProps) => {
	const {
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
		onTeamClick,
		lineStyle,
	} = props

	const [page, setPage] = useState(0)

	useEffect(() => {
		console.log('useEffect')
		// try to determine page from matchTree
		if (!matchTree.anyPicked()) {
			console.log('no matches picked')
			return
		}
		if (matchTree.allPicked()) {
			console.log('all matches picked')
			return setPage((matchTree.rounds.length - 1) * 2)
		}
		// find first unpicked match
		const firstUnpickedMatch = matchTree.findMatch(match => !match.isPicked())
		if (!firstUnpickedMatch) {
			console.log('no unpicked matches')
			return
		}
		console.log('first unpicked match', firstUnpickedMatch)
		const { roundIndex, matchIndex } = firstUnpickedMatch
		const numMatches = matchTree.rounds[roundIndex].matches.length
		let pageNum = roundIndex * 2
		if (matchIndex >= numMatches / 2) {
			pageNum++
		}
		console.log('setting page', pageNum)
		setPage(pageNum)
	}, [])


	const numRounds = matchTree.rounds.length
	const roundIndex = Math.floor(page / 2)
	const nextRoundIndex = roundIndex + 1
	const thisRoundIsLast = roundIndex === numRounds - 1
	const nextRoundIsLast = nextRoundIndex === numRounds - 1
	const leftSide = page % 2 === 0

	let matches1 = matchTree.rounds[roundIndex].matches
	let matches2 = thisRoundIsLast ? null : matchTree.rounds[nextRoundIndex].matches


	if (!thisRoundIsLast) {
		const mid1 = matches1.length / 2
		const mid2 = matches2.length / 2
		console.log('mid1', mid1)
		console.log('mid2', mid2)

		if (leftSide) {
			// if left side, get first half of matches
			matches1 = matches1.slice(0, mid1)
			matches2 = nextRoundIsLast ? matches2 : matches2.slice(0, mid2)
		} else {
			// if right side, get second half of matches
			matches1 = matches1.slice(mid1)
			matches2 = nextRoundIsLast ? matches2 : matches2.slice(mid2)
		}
		console.log('matches1', matches1)
		console.log('matches2', matches2)
	}

	const depth = numRounds - roundIndex - 1
	const matchGap1 = getFirstRoundMatchGap(numRounds)
	const teamGap = getTeamGap(depth)
	const teamHeight = getTeamHeight(numRounds)
	const teamWidth = getTeamWidth(numRounds)
	const teamFontSize = getTeamFontSize(numRounds)

	const matchHeight = teamHeight * 2 + teamGap
	const matchGap2 = getSubsequentMatchGap(matchHeight, matchGap1, matchHeight)

	const thisMatchPosition = leftSide ? 'left' : 'right'
	const nextMatchPosition = nextRoundIsLast ? 'center' : leftSide ? 'left' : 'right'

	const matchCol1 =
		<MatchColumnComponent
			matches={matches1}
			matchPosition={thisMatchPosition}
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

	const matchCol2 = thisRoundIsLast ? null :
		<MatchColumnComponent
			matches={matches2}
			matchPosition={nextMatchPosition}
			matchTree={matchTree}
			MatchBoxComponent={MatchBoxComponent}
			TeamSlotComponent={TeamSlotComponent}
			matchGap={matchGap2}
			teamGap={teamGap}
			teamHeight={teamHeight}
			teamWidth={teamWidth}
			teamFontSize={teamFontSize}
		/>

	const darkMode = useContext(DarkModeContext);

	const linesStyle = lineStyle || {
		className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
	}

	const maxW = getBracketWidth(numRounds)

	const handleNext = () => {
		const maxPages = (matchTree.rounds.length - 1) * 2
		const newPage = page + 1
		if (newPage <= maxPages) {
			setPage(newPage)
		}
	}

	const disableNext = matches1.some(match => !match.isPicked())

	return (
		<div className={`tw-flex tw-flex-col tw-gap-48 tw-min-h-screen tw-w-[${maxW}px] tw-m-auto tw-py-60`}>
			<div className='tw-flex tw-justify-center'>
				<h2 className='tw-text-24 tw-font-700 tw-text-white'>{`Round ${roundIndex + 1}`}</h2>
			</div>
			{thisRoundIsLast &&
				<WinnerContainer
					match={matchTree.rounds[roundIndex].matches[0]}
					matchTree={matchTree}
					topText='Winner'
					TeamSlotComponent={TeamSlotComponent}
					gap={20}
					topTextFontSize={64}
				// topTextColor='dd-blue'
				// topTextColorDark='white'
				/>
			}
			<div className={`tw-flex tw-justify-${thisRoundIsLast ? 'center' : 'between'} tw-flex-grow`}>
				{leftSide ? matchCol1 : matchCol2}
				{leftSide ? matchCol2 : matchCol1}
				<BracketLines
					rounds={matchTree.rounds}
					style={linesStyle}
				/>
			</div>
			<ActionButton
				variant='white'
				disabled={disableNext}
				onClick={handleNext}
			>Next</ActionButton>
		</div>
	)
}