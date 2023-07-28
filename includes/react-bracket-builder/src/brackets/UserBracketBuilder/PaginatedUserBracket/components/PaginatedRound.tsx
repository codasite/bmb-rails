import React from 'react';
import { ActionButton } from '../../../shared/components/ActionButton'
import { MatchTree, Round, MatchNode, Team } from '../../../shared/models/MatchTree';
import { MatchColumn } from '../../../shared/components/MatchColumn'
import { Direction, bracketConstants } from '../../../shared/constants'
import { Nullable } from '../../../../utils/types';
import { useAppSelector, useAppDispatch } from '../../../shared/app/hooks'
import { nextPage, selectCurrentPage, selectNumPages } from '../../../shared/features/bracketNavSlice';
import { selectMatchTree } from '../../../shared/features/matchTreeSlice';
import {
	getTargetHeight,
	getTeamClassName,
	getMatchHeight,
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
	console.log('rounds', matchTree.rounds)
	const numRounds = matchTree.rounds.length
	const numDirections = 2 // 1 direction for single trees, 2 for double trees
	const directions = [Direction.TopLeft, Direction.TopRight]
	const roundPageMaxIndex = numRounds * numDirections - 1 // This is the index of the last page of the last round
	const roundPageOffset = 1 // Number of pages that have been added before the first round

	const roundPage = currentPage - roundPageOffset // The current page ignoring pages before the first round
	const roundIndex = Math.floor((roundPageMaxIndex - roundPage) / numDirections)
	const direction = directions[roundPage % numDirections]

	// const direction = directions[(roundPageMaxIndex - roundPage) % numDirections]
	const round = matchTree.rounds[roundIndex]
	let matches: Nullable<MatchNode>[] = []
	console.log('direction', direction)
	if (direction === Direction.TopLeft) {
		// if going from left to right, only show the first half of the matches
		matches = round.matches.slice(0, round.matches.length / 2)
	} else {
		// if going from right to left, only show the second half of matches
		matches = round.matches.slice(round.matches.length / 2, round.matches.length)
	}
	const targetHeight = getTargetHeight(numRounds)
	const firstRoundMatchHeight = getFirstRoundMatchHeight(targetHeight, numDirections, numRounds, teamHeight)
	const roundReverseIndex = numRounds - roundIndex - 1
	const totalMatchHeight = getTargetMatchHeight(firstRoundMatchHeight, roundReverseIndex)
	const matchHeight = getMatchHeight(round.depth)
	const matchSpacing = totalMatchHeight - matchHeight
	console.log('round', round)
	console.log('targetHeight', targetHeight)
	console.log('roundIndex', roundIndex)
	console.log('firstRoundMatchHeight', firstRoundMatchHeight)
	console.log('totalMatchHeight', totalMatchHeight)
	console.log('matchHeight', matchHeight)
	console.log('matchSpacing', matchSpacing)
	// const round = matchTree.rounds[numRounds - currentPage]

	// console.log('PaginatedRound', props)
	console.log('page', currentPage)
	// console.log('round', round)
	return (
		<div className={`wpbb-paginated-round`}>
			{/* <PaginatedRoundHeader title={round.name} /> */}
			<PaginatedRoundHeader title={`Page ${currentPage}`} />
			<div className={'wpbb-paginated-round-match-columns'}>
				<MatchColumn
					round={round}
					matches={matches}
					direction={direction}
					matchBoxHeight={matchHeight}
					matchBoxSpacing={matchSpacing}
				/>
				{/* <MatchColumn /> */}
			</div>
			<ActionButton label='NEXT' onClick={() => { goNext() }} variant='secondary' />

		</div>
	)
}