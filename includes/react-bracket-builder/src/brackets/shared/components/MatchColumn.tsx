import React, { } from 'react';
import { Nullable } from '../../../utils/types';
import { Round, MatchNode } from '../models/MatchTree';
import { MatchBox } from './MatchBox';
//@ts-ignore
import { Direction } from '../constants'
import { getMatchHeight } from '../utils'

interface MatchColumnProps {
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

export const MatchColumn = (props: MatchColumnProps) => {
	const {
		round,
		matches,
		direction,
		numDirections,
		targetHeight,
		updateRoundName,
		updateTeam,
		pickTeam,
		paddingBottom,
		bracketName,
	} = props
	// const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
	const canEdit = updateTeam !== undefined && updateRoundName !== undefined
	const matchHeight = getMatchHeight(round.depth)

	const buildMatches = () => {
		const matchBoxes = matches.map((match, i) => {
			const matchIndex =
				direction === Direction.TopLeft ||
					direction === Direction.BottomLeft ||
					direction === Direction.Center
					? i : i + matches.length
			return (
				<MatchBox
					match={match}
					direction={direction}
					height={matchHeight}
					spacing={i + 1 < matches.length ? targetHeight - matchHeight : 0} // Do not add spacing to the last match in the round column
					updateTeam={canEdit ? (left: boolean, name: string) => updateTeam(round.id, matchIndex, left, name) : undefined}
					pickTeam={pickTeam ? (left: boolean) => pickTeam(matchIndex, left) : undefined}
					roundIndex={round.depth}
					matchIndex={matchIndex}
					bracketName={bracketName}
				/>
			)
		})
		return matchBoxes
	}
	const finalRound = round.depth === 0
	const pickedWinner = round.matches[0]?.result ? true : false
	let items = buildMatches()
	if (finalRound) {
		const finalMatch = round.matches[0]
		// find the team box to align the final match to
		const alignTeam = document.getElementsByClassName('wpbb-team-1-1-left') // should generate this with function
		// const alignBox = alignTeam.getBoundingClientRect()
		// console.log('alignBox', alignBox)
	}
	// const winner = 
	// 		<TeamSlot
	// 			className={'wpbb-final-winner' + (pickedWinner ? ' wpbb-match-winner' : '')}
	// 			team={finalRound.matches[0]?.result}
	// 		/> 


	return (
		<div className='wpbb-round'>
			{/* <RoundHeader round={round} updateRoundName={canEdit ? updateRoundName : undefined} /> */}
			<div className={'wpbb-round__body'} style={{ paddingBottom: paddingBottom }}>
				{items}
			</div>
		</div>
	)
}