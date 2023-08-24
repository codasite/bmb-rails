import React, { useRef } from 'react';
import { MatchTree, Round } from '../../shared/models/MatchTree'
import { bracketConstants } from '../../shared/constants';
import './user-template-bracket.scss'
import { MatchColumn } from '../../shared/components/Bracket';
// Direction enum
enum Direction {
	TopLeft = 0,
	TopRight = 1,
	Center = 2,
	BottomLeft = 3,
	BottomRight = 4,
}

interface BracketProps {
	matchTree: MatchTree;
	canEdit?: boolean;
	canPick?: boolean;
	setMatchTree?: (matchTree: MatchTree) => void;
}

export const UserTemplateBracket = (props: BracketProps) => {
	const {
		matchTree,
		setMatchTree,
	} = props

	const rounds = matchTree.rounds
	const canEdit = setMatchTree !== undefined && props.canEdit
	const canPick = setMatchTree !== undefined && props.canPick


	const updateRoundName = (roundId: number, name: string) => {
		if (!canEdit) {
			return
		}
		const newMatchTree = matchTree.clone();
		const roundToUpdate = newMatchTree.rounds.find((round) => round.id === roundId);
		if (roundToUpdate) {
			roundToUpdate.name = name;
			setMatchTree(newMatchTree);
		}
	};

	const updateTeam = (depth: number, matchIndex: number, left: boolean, name: string) => {
        return (MatchTree.updateTeam(canEdit,matchTree,depth,matchIndex,left,name))
	}

	const pickTeam = (depth: number, matchIndex: number, left: boolean) => {
		if (!canPick) {
			return
		}
		const newMatchTree = matchTree.clone()
		newMatchTree.advanceTeam(depth, matchIndex, left)
		setMatchTree(newMatchTree)
	}

	const bracketRef = useRef<HTMLDivElement>(null)

	/**
	 * Build rounds in two directions, left to right and right to left
	 */
	const buildRounds2 = (rounds: Round[]) => {
		// Assume rounds are sorted by depth
		// Rendering from left to right, sort by depth descending
		const numDirections = 2

		return [
			...rounds.slice(1).reverse().map((round, idx) => {
				// Get the first half of matches for this column
				const colMatches = round.matches.slice(0, round.matches.length / 2)

				return <MatchColumn
					totalRound={rounds.length}
					matches={colMatches}
					round={round} direction={Direction.TopLeft}
					numDirections={numDirections}
					matchHeight={2 * bracketConstants.teamHeight }
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			}),
			// handle final round differently
			<MatchColumn
				totalRound={rounds.length}
				matches={rounds[0].matches}
				round={rounds[0]}
				direction={Direction.Center}
				numDirections={numDirections}
				matchHeight={2 * bracketConstants.teamHeight}
				updateRoundName={canEdit ? updateRoundName : undefined}
				updateTeam={canEdit ? updateTeam : undefined}
				pickTeam={canPick ?
					(_, left: boolean) => pickTeam(0, 0, left)
					: undefined}
			/>,
			...rounds.slice(1).map((round, idx, arr) => {
				// Get the second half of matches for this column
				const colMatches = round.matches.slice(round.matches.length / 2)

				return <MatchColumn 
					totalRound={rounds.length}
					round={round}
					matches={colMatches}
					direction={Direction.TopRight}
					numDirections={numDirections}
					matchHeight={2 * bracketConstants.teamHeight}
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			})
		]
	}


	return (
		<>
			<div className='wpbb-bracket' ref={bracketRef}>
				{rounds.length > 0 && buildRounds2(rounds)}
			</div>
		</>
	)
}
