import React, { useState } from 'react';
import { ActionButton } from '../../../shared/components/ActionButton'
import LineTo, { SteppedLineTo } from 'react-lineto';
import { MatchTree, Round, MatchNode, Team } from '../../../shared/models/MatchTree';
import { MatchColumn } from '../../../shared/components/MatchColumn'
import { Direction, bracketConstants } from '../../../shared/constants'
import { Nullable } from '../../../../utils/types';
import { useAppSelector, useAppDispatch } from '../../../shared/app/hooks'
import { nextPage, selectCurrentPage, selectNumPages } from '../../../shared/features/bracketNavSlice';
import { setMatchTree, selectMatchTree } from '../../../shared/features/matchTreeSlice';
import {
	getTargetHeight,
	getTeamClasses,
	getUniqueTeamClass,
	getMatchBoxHeight,
	getFirstRoundMatchHeight,
	getTargetMatchHeight,
} from '../../../shared/utils';

const {
	teamHeight,
} = bracketConstants


const PaginatedRoundHeader = (props) => {
	const {
		title,
	} = props;

	return (
		<div className={'wpbb-paginated-round-header'}>
			<span className={'wpbb-paginated-round-header-text'}>{title}</span>
		</div>
	)
}

interface PaginatedRoundProps {
	round: Round;
	matches: Nullable<MatchNode>[];
	direction: Direction;
	numDirections: number;
	targetHeight: number;
	updateRoundName?: (roundId: number, name: string) => void;
	updateTeam?: (roundId: number, matchIndex: number, left: boolean, name: string) => void;
	pickTeam?: (matchIndex: number, left: boolean) => void;
	paddingBottom?: number;
	bracketName?: string;
}

// export const PaginatedRound = (props: PaginatedRoundProps) => {
export const PaginatedRound = (props) => {
	// const {
	// 	round,
	// 	matches,
	// } = props;
	const {
		canPick,
	} = props;

	const [allPicked, setAllPicked] = useState(false)

	const currentPage = useAppSelector(selectCurrentPage)
	const numPages = useAppSelector(selectNumPages)
	const dispatch = useAppDispatch()
	const goNext = () => dispatch(nextPage())
	const matchTree = useAppSelector(selectMatchTree)
	if (!matchTree) {
		return null
	}
	const numRounds = matchTree.rounds.length
	const numDirections = 2 // 1 direction for single trees, 2 for double trees
	const directions = [Direction.TopLeft, Direction.TopRight]
	const numRoundPages = numRounds * numDirections - 1 // This is the index of the last page of the last round
	const roundPageOffset = 1 // Number of pages that have been added before the first round

	const roundPage = currentPage - roundPageOffset // The current page ignoring pages before the first round
	const roundIndex = Math.floor((numRoundPages - roundPage) / numDirections)
	const direction = directions[roundPage % numDirections]

	const round1 = matchTree.rounds[roundIndex] // The round teams will be selected from
	const lastPage = roundPage === numRoundPages - 1 // Whether this is the last final selection page
	const round2 = lastPage ? null : matchTree.rounds[roundIndex - 1] // The round to fill. If this is the last page, there is no next round to fill

	const targetHeight = getTargetHeight(numRounds)
	const firstRoundMatchHeight = getFirstRoundMatchHeight(targetHeight, numDirections, numRounds, teamHeight)

	const round1ReverseIndex = numRounds - roundIndex - 1
	const round2ReverseIndex = numRounds - roundIndex

	const pickTeam = (matchIndex: number, left: boolean) => {
		if (!canPick) {
			return
		}
		matchTree.advanceTeam(round1.depth, matchIndex, left)
		dispatch(setMatchTree(matchTree.toSerializable()))
	}

	const onActionButtonPressed = () => {
		setAllPicked(false)
		goNext()
	}

	const matchColumn1 = <PaginatedMatchColumn
		round={round1}
		direction={direction}
		firstRoundMatchHeight={firstRoundMatchHeight}
		reverseIndex={round1ReverseIndex}
		showBracketLogo={false}
		showWinnerContainer={lastPage}
		pickTeam={pickTeam}
		onAllPicked={() => setAllPicked(true)}
	/>
	const matchColumn2 = round2 ? <PaginatedMatchColumn
		round={round2}
		direction={direction}
		firstRoundMatchHeight={firstRoundMatchHeight}
		reverseIndex={round2ReverseIndex}
		showBracketLogo={false}
		showWinnerContainer={false}
		paddingBottom={round2.depth === 0 ? getMatchBoxHeight(1) * 2 : undefined}
	/> : null

	return (
		<div className='wpbb-paginated-round'>
			<PaginatedRoundHeader title={round1.name} />
			<div className='wpbb-paginated-round-match-columns'>
				{direction === Direction.TopLeft ? matchColumn1 : matchColumn2}
				{direction === Direction.TopLeft ? matchColumn2 : matchColumn1}
			</div>
			<PaginatedBracketLines
				roundIdx={roundIndex}
				numDirections={numDirections}
				side={direction}
			/>
			<ActionButton label='NEXT' onClick={onActionButtonPressed} variant='secondary' disabled={!allPicked} />

		</div>
	)
}

interface PaginatedMatchColumnProps {
	round: Round;
	direction: Direction;
	firstRoundMatchHeight: number;
	reverseIndex: number;
	showBracketLogo?: boolean;
	showWinnerContainer?: boolean;
	paddingBottom?: number;
	pickTeam?: (matchIndex: number, left: boolean) => void;
	onAllPicked?: () => void;
}

const PaginatedMatchColumn = (props: PaginatedMatchColumnProps) => {
	const {
		round,
		direction,
		firstRoundMatchHeight,
		reverseIndex,
		paddingBottom,
		pickTeam,
		onAllPicked,
		showBracketLogo = false,
		showWinnerContainer = false,
	} = props

	const [matchStart, matches] = getMatches(round, direction)
	const allPicked = matches.every(match => {
		if (match === null) {
			return true
		}
		return match.result !== null
	})
	if (allPicked && onAllPicked) {
		onAllPicked()
	}
	const totalMatchHeight = getTargetMatchHeight(firstRoundMatchHeight, reverseIndex)
	const matchHeight = getMatchBoxHeight(round.depth)
	const matchSpacing = totalMatchHeight - matchHeight
	return (
		<MatchColumn
			round={round}
			matchStartIndex={matchStart}
			matches={matches}
			direction={direction}
			matchBoxHeight={matchHeight}
			matchBoxSpacing={matchSpacing}
			showBracketLogo={showBracketLogo}
			showWinnerContainer={showWinnerContainer}
			paddingBottom={paddingBottom}
			pickTeam={pickTeam}
		/>
	)
}

interface PaginatedBracketLinesProps {
	roundIdx: number,
	numDirections: number,
	side: Direction
}

const PaginatedBracketLines = (props: PaginatedBracketLinesProps) => {
	const {
		roundIdx,
		numDirections,
		side,
	} = props
	// Lines are always drawn from left to right so these two variables never change for horizontal lines
	const fromAnchor = 'right';
	const toAnchor = 'left';
	const style = {
		className: 'wpbb-bracket-line',
		delay: true,
		// borderColor: darkMode ? '#FFFFFF' : darkBlue,
		// borderStyle: 'solid',
		// borderWidth: 1,
	};
	let lines: JSX.Element[] = []

	if (roundIdx === 0) {
		// Final round, draw vertical lines connecting final match to winner
		const pairs: TeamClassPair[] = [
			{ fromTeam: 'wpbb-final-winner', toTeam: 'wpbb-team-0-0-left' },
			{ fromTeam: 'wpbb-team-0-0-left', toTeam: 'wpbb-team-0-0-right' },
		]
		lines = pairs.map(pair => {
			return (
				<LineTo
					from={pair.fromTeam}
					to={pair.toTeam}
					fromAnchor={'bottom'}
					toAnchor={'top'}
					within={'wpbb-bracket-lines-container'}
					{...style}
				/>
			)
		})
	} else {
		// Not final round, draw horizontal lines connecting matches
		const pairs = getTeamClassPairsForRoundSide(roundIdx, numDirections, side)
		lines = pairs.map(pair => {
			return (
				<SteppedLineTo
					from={pair.fromTeam}
					to={pair.toTeam}
					fromAnchor={fromAnchor}
					toAnchor={toAnchor}
					within={'wpbb-bracket-lines-container'}
					orientation='h'
					{...style}
				/>
			)
		})
	}
	return (
		<div className='wpbb-bracket-lines-container'>
			{lines}
		</div>
	)
}

interface TeamClassPair {
	fromTeam: string;
	toTeam: string;
}

function getTeamClassPairsForRoundSide(roundIdx: number, numDirections: number, side: Direction): TeamClassPair[] {
	let teamClassPairs: TeamClassPair[] = []
	const [matchStart, matchEnd] = getMatchRange(roundIdx, numDirections, side)

	// If building the right hand side, teams will be swapped so that lines are always drawn left to right
	const swapTeams = side === Direction.TopRight

	for (let matchIdx = matchStart; matchIdx < matchEnd; matchIdx++) {
		const thisTeamLeft = getUniqueTeamClass(roundIdx, matchIdx, true)
		const thisTeamRight = getUniqueTeamClass(roundIdx, matchIdx, false)
		const nextTeamIsLeft = matchIdx % 2 === 0
		const nextTeam = getUniqueTeamClass(roundIdx - 1, Math.floor(matchIdx / 2), nextTeamIsLeft)
		teamClassPairs.push(buildClassPair(thisTeamLeft, nextTeam, swapTeams))
		teamClassPairs.push(buildClassPair(thisTeamRight, nextTeam, swapTeams))
	}
	return teamClassPairs
}

function getMatchRange(roundIdx: number, numDirections: number, side: Direction): [number, number] {
	const numMatches = Math.pow(2, roundIdx)
	let startIdx = 0
	if (side === Direction.TopRight) {
		startIdx = numMatches / 2
	}
	let endIdx = numMatches
	if (side === Direction.TopLeft) {
		endIdx = numMatches / numDirections
	}
	return [startIdx, endIdx]
}

function buildClassPair(fromTeam: string, toTeam: string, swapTeams: boolean): TeamClassPair {
	return {
		fromTeam: swapTeams ? toTeam : fromTeam,
		toTeam: swapTeams ? fromTeam : toTeam,
	}
}

function getMatches(round, direction): [number, Nullable<MatchNode>[]] {
	let startIdx = 0
	let matches: Nullable<MatchNode>[] = []
	if (direction === Direction.TopLeft) {
		// if going from left to right, only show the first half of the matches
		matches = round.matches.slice(startIdx, Math.ceil(round.matches.length / 2))
	} else {
		startIdx = Math.floor(round.matches.length / 2)
		matches = round.matches.slice(startIdx, round.matches.length)
	}
	return [startIdx, matches]
}