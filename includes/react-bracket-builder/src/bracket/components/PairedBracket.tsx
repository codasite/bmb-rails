import React, { useState, useEffect, useRef, forwardRef } from 'react';
import { Button, InputGroup } from 'react-bootstrap';
import { Form } from 'react-bootstrap';
import { Nullable } from '../../utils/types';
import { MatchTree, Round, MatchNode, Team } from '../models/MatchTree';
import LineTo, { SteppedLineTo, Line } from 'react-lineto';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../assets/BMB-ICON-WHITE.svg';
// import html2canvas

const teamHeight = 20

const defaultMatchGap = 20
const depth4MatchGap = 12
const depth5MatchGap = 4

const fourRoundHeight = 583
const fiveRoundHeight = 806
const sixRoundHeight = 1100
// const sixRoundHeight = 854

const targetRoundHeights = [
	fourRoundHeight,
	fourRoundHeight,
	fourRoundHeight,
	fourRoundHeight,
	fiveRoundHeight,
	sixRoundHeight,
]

const getTargetHeight = (numRounds: number) => {
	return targetRoundHeights[numRounds - 1]
}

const getMatchHeight = (depth: number) => {
	let gap = teamHeight
	if (depth === 4) {
		gap += depth4MatchGap
	} else if (depth === 5) {
		gap += depth5MatchGap
	} else {
		gap += defaultMatchGap
	}
	return gap
}

const getTeamClassName = (roundIndex, matchIndex, left) => {
	const className = `wpbb-team${left ? '1' : '2'} wpbb-team-${roundIndex}-${matchIndex}-${left ? 'left' : 'right'}`
	return className
}



// Direction enum
enum Direction {
	TopLeft = 0,
	TopRight = 1,
	Center = 2,
	BottomLeft = 3,
	BottomRight = 4,
}


interface TeamSlotProps {
	className?: string;
	team?: Team | null;
	updateTeam?: (name: string) => void;
	pickTeam?: () => void;
	roundIndex?: number;
	matchIndex?: number;
	left?: boolean;
	winner?: boolean;
}

const TeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')

	const {
		team,
		updateTeam,
		pickTeam,
		roundIndex,
		matchIndex,
		left,
		winner,
	} = props

	const className = props.className ? props.className : getTeamClassName(roundIndex, matchIndex, left) + (winner ? ' wpbb-match-winner' : '')

	const startEditing = () => {
		if (!updateTeam) {
			return
		}
		setEditing(true)
		setTextBuffer(team ? team.name : '')
	}

	const doneEditing = (e) => {
		if (!updateTeam) {
			return
		}
		if (!team && textBuffer !== '' || team && textBuffer !== team.name) {
			updateTeam(textBuffer)
		}
		setEditing(false)
	}

	const handleClick = (e) => {
		if (updateTeam) {
			startEditing()
		} else if (pickTeam) {
			pickTeam()
		}
	}

	return (
		<div className={className} onClick={handleClick}>
			{editing ?
				<input
					className='wpbb-team-name-input'
					autoFocus
					onFocus={(e) => e.target.select()}
					type='text'
					value={textBuffer}
					onChange={(e) => setTextBuffer(e.target.value)}
					onBlur={doneEditing}
					onKeyUp={(e) => {
						if (e.key === 'Enter') {
							doneEditing(e)
						}
					}}
				/>
				:
				<span className='wpbb-team-name'>{team ? team.name : ''}</span>
				// <span className='wpbb-team-name'>{roundIndex}-{matchIndex}-{left ? 'left' : 'right'}</span>
				// <span className='wpbb-team-name'>Team</span>
			}
		</div>
	)
}

interface MatchBoxProps {
	match: MatchNode | null;
	direction: Direction;
	height: number;
	spacing: number;
	updateTeam?: (left: boolean, name: string) => void;
	pickTeam?: (left: boolean) => void;
	roundIndex: number;
	matchIndex: number;
}

const MatchBox = (props: MatchBoxProps) => {
	const {
		match,
		direction,
		height,
		spacing,
		updateTeam,
		pickTeam,
	} = props

	if (match === null) {
		return (
			<div className='wpbb-match-box-empty' style={{ height: height + spacing }} />
		)
	}
	let className: string;

	if (direction === Direction.TopLeft || direction === Direction.BottomLeft) {
		// Left side of the bracket
		className = 'wpbb-match-box-left'
	} else if (direction === Direction.TopRight || direction === Direction.BottomRight) {
		// Right side of the bracket
		className = 'wpbb-match-box-right'
	} else {
		className = 'wpbb-match-box-center'
	}

	const upperOuter = match.left === null
	const lowerOuter = match.right === null

	if (upperOuter && lowerOuter) {
		// First round
		className += '-outer'
	} else if (upperOuter) {
		// Upper bracket
		className += '-outer-upper'
	} else if (lowerOuter) {
		// Lower bracket
		className += '-outer-lower'
	}

	const team1Wins = match.result && match.result === match.team1 ? true : false
	const team2Wins = match.result && match.result === match.team2 ? true : false

	return (
		<div className={className} style={{ height: height, marginBottom: spacing }}>
			<TeamSlot
				// className='wpbb-team1'
				winner={team1Wins}
				team={match.team1}
				updateTeam={updateTeam ? (name: string) => updateTeam(true, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(true) : undefined}
				roundIndex={props.roundIndex}
				matchIndex={props.matchIndex}
				left={true}
			/>
			<TeamSlot
				// className='wpbb-team2'
				winner={team2Wins}
				team={match.team2}
				updateTeam={updateTeam ? (name: string) => updateTeam(false, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(false) : undefined}
				roundIndex={props.roundIndex}
				matchIndex={props.matchIndex}
				left={false}
			/>
		</div>
	)
}

interface RoundHeaderProps {
	round: Round;
	updateRoundName?: (roundId: number, name: string) => void;
}

const RoundHeader = (props: RoundHeaderProps) => {
	const [editRoundName, setEditRoundName] = useState(false);
	const [nameBuffer, setNameBuffer] = useState('');
	const {
		round,
		updateRoundName,
	} = props

	const canEdit = updateRoundName !== undefined

	useEffect(() => {
		setNameBuffer(props.round.name)
	}, [props.round.name])

	const startEditing = () => {
		if (!canEdit) {
			return
		}
		setEditRoundName(true)
		setNameBuffer(round.name)
	}

	const doneEditing = () => {
		if (!canEdit) {
			return
		}
		setEditRoundName(false)
		updateRoundName(props.round.id, nameBuffer)
	}

	return (
		<div className='wpbb-round__header'>
			{editRoundName ? <Form.Control type='text'
				value={nameBuffer}
				autoFocus
				onFocus={(e) => e.target.select()}
				onBlur={() => doneEditing()}
				onChange={(e) => setNameBuffer(e.target.value)}
				onKeyUp={(e) => {
					if (e.key === 'Enter') {
						doneEditing()
					}
				}}
			/>
				:
				<span onClick={startEditing}>{round.name}</span>
			}
		</div>
	)
}

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
}

const MatchColumn = (props: MatchColumnProps) => {
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

interface PairedBracketProps {
	matchTree: MatchTree;
	canEdit?: boolean;
	canPick?: boolean;
	setMatchTree?: (matchTree: MatchTree) => void;
}

export const PairedBracket = (props: PairedBracketProps) => {
	const {
		matchTree,
		setMatchTree,
	} = props

	const [dimensions, setDimensions] = useState({
		height: window.innerHeight,
		width: window.innerWidth,
	})

	useEffect(() => {
		const handleResize = () => {
			setDimensions({
				height: window.innerHeight,
				width: window.innerWidth,
			})

		}
		window.addEventListener('resize', handleResize)
		return () => {
			window.removeEventListener('resize', handleResize)
		}
	}, [])

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

	// The number of rounds sets the initial height of each match
	// const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 1);
	const numDirections = 2
	const maxMatchesPerRound = 2 ** (rounds.length - 1)
	const maxMatchesPerColumn = maxMatchesPerRound / numDirections
	let firstRoundMatchHeight = targetHeight / maxMatchesPerColumn
	firstRoundMatchHeight += (firstRoundMatchHeight - teamHeight) / maxMatchesPerColumn // Divvy up spacing that would be added after the last match in the column


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
				const targetHeight = 2 ** idx * firstRoundMatchHeight // the target match height doubles for each consecutive round

				return <MatchColumn
					matches={colMatches}
					round={round} direction={Direction.TopLeft}
					numDirections={numDirections}
					// targetHeight={2 ** idx * firstRoundMatchHeight}
					targetHeight={targetHeight}
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			}),
			// handle final round differently
			<MatchColumn
				matches={rounds[0].matches}
				round={rounds[0]}
				direction={Direction.Center}
				numDirections={numDirections}
				targetHeight={targetHeight / 4}
				updateRoundName={canEdit ? updateRoundName : undefined}
				updateTeam={canEdit ? updateTeam : undefined}
				pickTeam={canPick ?
					(_, left: boolean) => pickTeam(0, 0, left)
					: undefined}
				paddingBottom={getMatchHeight(1) * 2} // offset the final match by the height of the penultimate round
			/>,
			...rounds.slice(1).map((round, idx, arr) => {
				// Get the second half of matches for this column
				const colMatches = round.matches.slice(round.matches.length / 2)
				// The target height decreases by half for each consecutive round in the second half of the bracket
				const targetHeight = 2 ** (arr.length - 1 - idx) * firstRoundMatchHeight

				return <MatchColumn round={round}
					matches={colMatches}
					direction={Direction.TopRight}
					numDirections={numDirections}
					// targetHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight}
					targetHeight={targetHeight}
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
			within='wpbb-bracket'
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
			const team1 = getTeamClassName(roundIdx + 1, matchIdx * 2 + (side === 'right' ? 1 : 0), true);
			const team2 = getTeamClassName(roundIdx + 1, matchIdx * 2 + (side === 'right' ? 1 : 0), false);

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
			delay: true,
			borderColor: '#FFFFFF',
			borderStyle: 'solid',
			borderWidth: 1,
		};

		rounds.forEach((round, roundIdx) => {
			round.matches.forEach((match, matchIdx) => {
				if (!match) {
					return;
				}

				const team1 = getTeamClassName(roundIdx, matchIdx, true)
				const team2 = getTeamClassName(roundIdx, matchIdx, false)
				// Whether the matches appear on the left or right side of the bracket
				// This determines the direction of the lines
				const team1LeftSide = matchIdx < round.matches.length / 2;
				// The second team in the first match of the first round is on the opposite side
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
						within='wpbb-bracket'
						{...style}
					/>,
					<LineTo
						from='wpbb-final-winner'
						to={team1}
						fromAnchor='bottom'
						toAnchor='top'
						within='wpbb-bracket'
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
			<div className={'wpbb-slogan-container' + (pickedWinner ? ' invisible' : ' visible')}>
				<span className={'wpbb-slogan-text'}>WHO YOU GOT?</span>
			</div>,
			<div className='wpbb-winner-container'>
				<span className={'wpbb-winner-text' + (pickedWinner ? ' visible' : ' invisible')}>WINNER</span>
				<TeamSlot
					className={'wpbb-final-winner' + (pickedWinner ? ' wpbb-match-winner' : '')}
					team={finalRound.matches[0]?.result}
				/>
			</div>,
			<BracketLogo className="wpbb-bracket-logo" />

		]
		return positioned
	}


	return (
		<>
			<div className={`wpbb-bracket wpbb-paired-${numRounds}`}>
				{rounds.length > 0 && buildRounds2(rounds)}
				{renderLines(rounds)}
				{renderPositioned(rounds)}
			</div>
			{/* <Button variant='primary' onClick={screenshot}>ref</Button> */}
		</>
	)
};