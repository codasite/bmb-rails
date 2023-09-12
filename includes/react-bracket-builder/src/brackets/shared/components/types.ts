import { Round, MatchNode, Team } from '../models/MatchTree';
import { Nullable } from '../../../utils/types';
import { MatchTree } from '../models/MatchTree';
import { Match } from '@sentry/react/types/reactrouterv3';

export interface TeamSlotProps {
	team?: Nullable<Team>;
	match: MatchNode;
	matchPosition: string;
	teamPosition: string;
	height: number;
	width?: number;
	fontSize?: number;
	fontWeight?: number;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	getTeamClass?: (roundIndex: number, matchIndex: number, position: string) => string;
}

export interface MatchBoxProps {
	match: Nullable<MatchNode>;
	matchPosition: string;
	matchTree: MatchTree;
	teamGap: number;
	teamHeight: number;
	setMatchTree?: (matchTree: MatchTree) => void;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}

export interface MatchColumnProps {
	matches: Nullable<MatchNode>[];
	matchPosition: string;
	teamGap: number;
	teamHeight: number;
	matchGap: number;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	MatchBoxComponent: React.FC<MatchBoxProps>;
	TeamSlotComponent: React.FC<TeamSlotProps>;
}

export interface BracketProps {
	height: number;
	width: number;
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


