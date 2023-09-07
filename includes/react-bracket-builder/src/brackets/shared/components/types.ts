import { Round, MatchNode, Team } from '../models/MatchTree';
import { Nullable } from '../../../utils/types';
import { MatchTree } from '../models/MatchTree';
import { Match } from '@sentry/react/types/reactrouterv3';

export interface TeamSlotProps {
	team?: Nullable<Team>;
	match: MatchNode;
	position: string;
	teamHeight: number;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	getTeamClass?: (roundIndex: number, matchIndex: number, left: boolean) => string;
}

export interface MatchBoxProps {
	match: Nullable<MatchNode>;
	position: string;
	matchTree: MatchTree;
	teamGap: number;
	teamHeight: number;
	setMatchTree?: (matchTree: MatchTree) => void;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}

export interface MatchColumnProps {
	matches: Nullable<MatchNode>[];
	position: string;
	teamGap: number;
	teamHeight: number;
	matchGap: number;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	MatchBoxComponent: React.FC<MatchBoxProps>;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}

export interface BracketProps {
	targetHeight: number;
	teamHeight: number;
	teamGap: number;
	matchTree: MatchTree;
	// bracketName?: string;
	// canEdit?: boolean;
	// canPick?: boolean;
	// darkMode?: boolean;
	setMatchTree?: (matchTree: MatchTree) => void;
	MatchColumnComponent: React.FC<MatchColumnProps>;
	MatchBoxComponent: React.FC<MatchBoxProps>;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}


