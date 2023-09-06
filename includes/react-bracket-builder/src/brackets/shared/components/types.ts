import { Round, MatchNode, Team } from '../models/MatchTree';
import { Nullable } from '../../../utils/types';
import { MatchTree } from '../models/MatchTree';
import { Match } from '@sentry/react/types/reactrouterv3';

export interface TeamSlotProps {
	team?: Nullable<Team>;
	match?: Nullable<MatchNode>;
	position: string;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
}

export interface MatchBoxProps {
	match: Nullable<MatchNode>;
	position: string;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}

export interface MatchColumnProps {
	matches: Nullable<MatchNode>[];
	position: string;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	MatchBoxComponent: React.FC<MatchBoxProps>;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}

