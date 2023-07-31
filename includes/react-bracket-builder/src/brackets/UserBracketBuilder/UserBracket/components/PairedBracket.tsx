import React, { useState, useEffect } from 'react';
import { MatchTree, Round, MatchNode, Team } from '../../../shared/models/MatchTree';
import LineTo, { SteppedLineTo } from 'react-lineto';
import { useWindowDimensions } from '../../../../utils/hooks';
//@ts-ignore
import { MatchColumn } from '../../../shared/components/MatchColumn'
import { Direction, bracketConstants } from '../../../shared/constants'
import {
	getTargetHeight,
	getTeamClasses,
	getUniqueTeamClass,
	getMatchBoxHeight,
	getFirstRoundMatchHeight,
	getTargetMatchHeight,
} from '../../../shared/utils'

const {
	teamHeight,
} = bracketConstants

interface PairedBracketProps {
	matchTree: MatchTree;
	bracketName?: string;
	canEdit?: boolean;
	canPick?: boolean;
	darkMode?: boolean;
	setMatchTree?: (matchTree: MatchTree) => void;
	scale?: number;
}

export const PairedBracket = (props: PairedBracketProps) => {
	const {
		matchTree,
		setMatchTree,
		darkMode,
		bracketName,
	} = props

	const dimensions = useWindowDimensions()

	const rounds = matchTree.rounds
	const numRounds = rounds.length
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

	const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
		if (!canEdit) {
			return
		}
		const newMatchTree = matchTree.clone();
		const roundToUpdate = newMatchTree.rounds.find((round) => round.id === roundId);
		if (roundToUpdate) {
			const matchToUpdate = roundToUpdate.matches[matchIndex];
			if (matchToUpdate) {
				if (left) {
					const team = matchToUpdate.team1;
					if (team) {
						team.name = name;
					} else {
						matchToUpdate.team1 = new Team(name);
					}
				} else {
					const team = matchToUpdate.team2;
					if (team) {
						team.name = name;
					} else {
						matchToUpdate.team2 = new Team(name);
					}
				}
			}
			setMatchTree(newMatchTree);
		}
	}

	const pickTeam = (depth: number, matchIndex: number, left: boolean) => {
		if (!canPick) {
			return
		}
		const newMatchTree = matchTree.clone()
		newMatchTree.advanceTeam(depth, matchIndex, left)
		setMatchTree(newMatchTree)
	}

	const targetHeight = getTargetHeight(numRounds)

	// // The number of rounds sets the initial height of each match
	// // const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 1);
	// const numDirections = 2
	// const maxMatchesPerRound = 2 ** (rounds.length - 1)
	// const maxMatchesPerColumn = maxMatchesPerRound / numDirections
	// let firstRoundMatchHeight = targetHeight / maxMatchesPerColumn
	// firstRoundMatchHeight += (firstRoundMatchHeight - teamHeight) / maxMatchesPerColumn 
	const firstRoundMatchHeight = getFirstRoundMatchHeight(targetHeight, 2, rounds.length, teamHeight)


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
				// const targetHeight = 2 ** idx * firstRoundMatchHeight // the target match height doubles for each consecutive round
				const totalMatchHeight = getTargetMatchHeight(firstRoundMatchHeight, idx)
				const matchHeight = getMatchBoxHeight(round.depth)
				const matchSpacing = totalMatchHeight - matchHeight

				return <MatchColumn
					bracketName={bracketName}
					matches={colMatches}
					round={round} direction={Direction.TopLeft}
					// targetHeight={2 ** idx * firstRoundMatchHeight}
					matchBoxHeight={matchHeight}
					matchBoxSpacing={matchSpacing}
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			}),
			// handle final round differently
			<MatchColumn
				bracketName={bracketName}
				matches={rounds[0].matches}
				round={rounds[0]}
				direction={Direction.Center}
				matchBoxHeight={getMatchBoxHeight(0)}
				// targetHeight={targetHeight / 4}
				updateRoundName={canEdit ? updateRoundName : undefined}
				updateTeam={canEdit ? updateTeam : undefined}
				pickTeam={canPick ?
					(_, left: boolean) => pickTeam(0, 0, left)
					: undefined}
				paddingBottom={getMatchBoxHeight(1) * 2} // offset the final match by the height of the penultimate round
			/>,
			...rounds.slice(1).map((round, idx, arr) => {
				// Get the second half of matches for this column
				const colMatches = round.matches.slice(round.matches.length / 2)
				// The target height decreases by half for each consecutive round in the second half of the bracket
				// const targetHeight = 2 ** (arr.length - 1 - idx) * firstRoundMatchHeight
				const totalMatchHeight = getTargetMatchHeight(firstRoundMatchHeight, arr.length - 1 - idx)
				const matchHeight = getMatchBoxHeight(round.depth)
				const matchSpacing = totalMatchHeight - matchHeight

				return <MatchColumn
					bracketName={bracketName}
					round={round}
					matches={colMatches}
					direction={Direction.TopRight}
					// targetHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight}
					matchBoxHeight={matchHeight}
					matchBoxSpacing={matchSpacing}
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			})
		]
	}

	// Helper function to create a SteppedLineTo JSX Element
	const createSteppedLine = (
		team1: string,
		team2: string,
		leftSide: boolean,
		fromAnchor: string,
		toAnchor: string,
		style: object
	): JSX.Element => (
		<SteppedLineTo
			from={leftSide ? team1 : team2} // Lines must be drawn from left to right to render properly
			to={leftSide ? team2 : team1}
			fromAnchor={fromAnchor}
			toAnchor={toAnchor}
			orientation='h'
			within='wpbb-bracket-lines-container'
			{...style}
		/>
	);

	// Function to handle the match side and draw the lines
	// This function takes in the match details, team details, anchor details and style, 
	// and returns an array of JSX elements for the lines to be drawn for a match
	const handleMatchSide = (
		match: MatchNode,
		roundIdx: number,
		matchIdx: number,
		side: keyof MatchNode,
		team: string,
		leftSide: boolean,
		fromAnchor: string,
		toAnchor: string,
		style: object
	): JSX.Element[] => {
		if (match[side]) {
			const team1 = getUniqueTeamClass(roundIdx + 1, matchIdx * 2 + (side === 'right' ? 1 : 0), true);
			const team2 = getUniqueTeamClass(roundIdx + 1, matchIdx * 2 + (side === 'right' ? 1 : 0), false);

			return [
				createSteppedLine(team1, team, leftSide, fromAnchor, toAnchor, style),
				createSteppedLine(team2, team, leftSide, fromAnchor, toAnchor, style),
			];
		}

		return [];
	};

	// Main function
	const renderLines = (rounds: Round[]): JSX.Element[] => {
		let lines: JSX.Element[] = [];
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

		rounds.forEach((round, roundIdx) => {
			round.matches.forEach((match, matchIdx) => {
				if (!match) {
					return;
				}

				const team1 = getUniqueTeamClass(roundIdx, matchIdx, true)
				const team2 = getUniqueTeamClass(roundIdx, matchIdx, false)
				// Whether the matches appear on the left or right side of the bracket
				// This determines the direction of the lines
				const team1LeftSide = matchIdx < round.matches.length / 2;
				// The second team in the last match of the last round is on the opposite side
				const team2LeftSide = roundIdx === 0 && matchIdx === 0 ? !team1LeftSide : team1LeftSide;

				lines = [
					...lines,
					...handleMatchSide(match, roundIdx, matchIdx, 'left', team1, team1LeftSide, fromAnchor, toAnchor, style),
					...handleMatchSide(match, roundIdx, matchIdx, 'right', team2, team2LeftSide, fromAnchor, toAnchor, style),
				];
				if (roundIdx === 0) {
					// Render lines for the final match
					lines = [...lines, <LineTo
						from={team1}
						to={team2}
						fromAnchor='bottom'
						toAnchor='top'
						within='wpbb-bracket-lines-container'
						{...style}
					/>,
					<LineTo
						from='wpbb-final-winner'
						to={team1}
						fromAnchor='bottom'
						toAnchor='top'
						within='wpbb-bracket-lines-container'
						{...style}
					/>,
					];
				}
			});
		});
		return lines;
	};


	const renderPositioned = (rounds: Round[]): JSX.Element[] => {
		const finalRound = rounds[0]
		const pickedWinner = finalRound.matches[0]?.result ? true : false
		const positioned = [

		]
		return positioned
	}


	return (
		<>
			<div className={`wpbb-bracket wpbb-paired-bracket wpbb-${numRounds}-rounds${darkMode ? ' wpbb-dark-mode' : ''}`}>
				<div className='wpbb-bracket-rounds-container'>
					{rounds.length > 0 && buildRounds2(rounds)}
				</div>
				<div className='wpbb-bracket-lines-container'>
					{renderLines(rounds)}
				</div>
				{renderPositioned(rounds)}
			</div>
			{/* <Button variant='primary' onClick={screenshot}>ref</Button> */}
		</>
	)
};