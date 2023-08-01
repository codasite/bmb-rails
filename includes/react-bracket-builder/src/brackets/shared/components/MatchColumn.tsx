import React, { } from 'react';
import { Nullable } from '../../../utils/types';
import { Round, MatchNode } from '../models/MatchTree';
import { MatchBox } from './MatchBox';
//@ts-ignore
import { Direction } from '../constants'
import { getMatchBoxHeight } from '../utils'

interface MatchColumnProps {
	round: Round;
	matchStartIndex?: number
	matches: Nullable<MatchNode>[];
	direction: Direction;
	matchBoxHeight: number;
	matchBoxSpacing?: number;
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
		matchBoxHeight,
		matchBoxSpacing,
		updateRoundName,
		updateTeam,
		pickTeam,
		paddingBottom,
		bracketName,
	} = props

	let matchStartIndex: number = 0
	if (props.matchStartIndex !== undefined) {
		matchStartIndex = props.matchStartIndex
	} else if (direction === Direction.TopRight) {
		matchStartIndex = matches.length
	}

	// const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
	const canEdit = updateTeam !== undefined && updateRoundName !== undefined

	const buildMatches = () => {
		const matchBoxes = matches.map((match, i) => {
			const matchIndex = matchStartIndex + i
			return (
				<MatchBox
					match={match}
					direction={direction}
					height={matchBoxHeight}
					spacing={i + 1 < matches.length && matchBoxSpacing !== undefined ? matchBoxSpacing : 0} // Do not add spacing to the last match in the round column
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
	let items = buildMatches()
	// console.log('items', items)

	return (
		<div className='wpbb-round'>
			{/* <RoundHeader round={round} updateRoundName={canEdit ? updateRoundName : undefined} /> */}
			<div className={'wpbb-round__body'} style={{ paddingBottom: paddingBottom }}>
				{items}
			</div>
		</div>
	)
}