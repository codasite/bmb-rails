import React from 'react';
import { ActionButton } from '../shared/ActionButton';
import { MatchTree, Round, MatchNode, Team } from '../../../bracket/models/MatchTree';
import { MatchColumn } from '../../../bracket/components/MatchColumn';
import { Direction } from '../../../bracket/constants';
import { Nullable } from '../../../utils/types';

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

export const PaginatedRound = (props: PaginatedRoundProps) => {
	const {
		round,
		matches,
	} = props;
	return (
		<div className={`wpbb-paginated-round`}>
			<PaginatedRoundHeader title={round.name} />
			<div className={'wpbb-paginated-round-content'}>
				{/* <MatchColumn />
				<MatchColumn /> */}
			</div>
			<ActionButton label='NEXT' onClick={() => { }} />

		</div>
	)
}