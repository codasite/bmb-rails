import React from 'react';
import { ActionButton } from '../../../shared/components/ActionButton'
import LineTo, { SteppedLineTo } from 'react-lineto';
import { MatchTree, Round, MatchNode, Team } from '../../../shared/models/MatchTree';
import { MatchColumn } from '../../../shared/components/MatchColumn'
import { Direction, bracketConstants } from '../../../shared/constants'
import { Nullable } from '../../../../utils/types';
import { useAppSelector, useAppDispatch } from '../../../shared/app/hooks'
import { nextPage, selectCurrentPage, selectNumPages } from '../../../shared/features/bracketNavSlice';
import { selectMatchTree } from '../../../shared/features/matchTreeSlice';
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
	const roundPageMaxIndex = numRounds * numDirections - 1 // This is the index of the last page of the last round
	const roundPageOffset = 1 // Number of pages that have been added before the first round

	const roundPage = currentPage - roundPageOffset // The current page ignoring pages before the first round
	const roundIndex = Math.floor((roundPageMaxIndex - roundPage) / numDirections)
	const direction = directions[roundPage % numDirections]

	const round1 = matchTree.rounds[roundIndex] // The round teams will be selected from
	const round2 = roundIndex > 0 ? matchTree.rounds[roundIndex - 1] : null // The round being filled

	const targetHeight = getTargetHeight(numRounds)
	const firstRoundMatchHeight = getFirstRoundMatchHeight(targetHeight, numDirections, numRounds, teamHeight)

	const round1ReverseIndex = numRounds - roundIndex - 1
	const round2ReverseIndex = numRounds - roundIndex

	const matchColumn1 = buildMatchColumn(round1, direction, firstRoundMatchHeight, round1ReverseIndex)
	const matchColumn2 = round2 ? buildMatchColumn(round2, direction, firstRoundMatchHeight, round2ReverseIndex) : null


	// const classes = getTeamClassNames(numRounds, numDirections)
	// const classes = getTeamClassPairs(numRounds, numDirections)
	// const classes = getTeamClassPairsForRoundSide(roundIndex, numDirections, direction)
	// console.log('classes', classes)
	const lines = renderLines(roundIndex, numDirections, direction)

	return (
		<div className='wpbb-paginated-round'>
			{/* <PaginatedRoundHeader title={round.name} /> */}
			<PaginatedRoundHeader title={`Page ${currentPage}`} />
			<div className='wpbb-paginated-round-match-columns'>
				{direction === Direction.TopLeft ? matchColumn1 : matchColumn2}
				{direction === Direction.TopLeft ? matchColumn2 : matchColumn1}
			</div>
			<div className='wpbb-bracket-lines-container'>
				{lines}
			</div>
			<ActionButton label='NEXT' onClick={() => { goNext() }} variant='secondary' />

		</div>
	)
}

function buildMatchColumn(round: Round, direction: Direction, firstRoundMatchHeight: number, reverseIndex: number) {
	const [matchStart, matches] = getMatches(round, direction)
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
		/>
	)

}

function renderLines(roundIdx: number, numDirections: number, side: Direction) {
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

	const pairs = getTeamClassPairsForRoundSide(roundIdx, numDirections, side)
	const lines = pairs.map(pair => {
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
	return lines
}

interface TeamClassPair {
	fromTeam: string;
	toTeam: string;
}

function getTeamClassNames(numRounds: number, numDirections: number): string[] {
	let teamClassNames: string[] = []
	for (let roundIdx = 0; roundIdx < numRounds; roundIdx++) {
		const numMatches = Math.pow(2, roundIdx)
		for (let matchIdx = 0; matchIdx < numMatches; matchIdx++) {
			teamClassNames.push(getUniqueTeamClass(roundIdx, matchIdx, true))
			teamClassNames.push(getUniqueTeamClass(roundIdx, matchIdx, false))
		}
	}
	return teamClassNames
}

function getTeamClassPairs(numRounds: number, numDirections: number): TeamClassPair[] {
	let teamClassPairs: TeamClassPair[] = []
	for (let roundIdx = numRounds - 1; roundIdx > 0; roundIdx--) {
		const numMatches = Math.pow(2, roundIdx)
		for (let matchIdx = 0; matchIdx < numMatches; matchIdx++) {
			const toLeftTeam = matchIdx % 2 === 0
			const toTeamClass = getUniqueTeamClass(roundIdx - 1, Math.floor(matchIdx / 2), toLeftTeam)
			teamClassPairs.push({
				fromTeam: getUniqueTeamClass(roundIdx, matchIdx, true),
				toTeam: toTeamClass
			})
			teamClassPairs.push({
				fromTeam: getUniqueTeamClass(roundIdx, matchIdx, false),
				toTeam: toTeamClass
			})
		}
	}
	return teamClassPairs
}

function getTeamClassPairsForRound(roundIdx: number, numDirections: number): TeamClassPair[] {
	let teamClassPairs: TeamClassPair[] = []
	const numMatches = Math.pow(2, roundIdx)
	for (let matchIdx = 0; matchIdx < numMatches; matchIdx++) {
		const toLeftTeam = matchIdx % 2 === 0
		const toTeamClass = getUniqueTeamClass(roundIdx - 1, Math.floor(matchIdx / 2), toLeftTeam)
		teamClassPairs.push({
			fromTeam: getUniqueTeamClass(roundIdx, matchIdx, true),
			toTeam: toTeamClass
		})
		teamClassPairs.push({
			fromTeam: getUniqueTeamClass(roundIdx, matchIdx, false),
			toTeam: toTeamClass
		})
	}
	return teamClassPairs
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