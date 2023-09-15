import { Round, MatchNode, Team } from '../models/MatchTree';
import { Nullable } from '../../../utils/types';
import { MatchTree } from '../models/MatchTree';
import { Match } from '@sentry/react/types/reactrouterv3';

export interface TeamSlotProps {
	team?: Nullable<Team>;
	match: MatchNode;
	matchPosition?: string;
	teamPosition?: string;
	height?: number;
	width?: number;
	fontSize?: number;
	fontWeight?: number;
	textColor?: string;
	backgroundColor?: string;
	borderColor?: string;
	matchTree: MatchTree;
	onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void;
	setMatchTree?: (matchTree: MatchTree) => void;
	getTeamClass?: (roundIndex: number, matchIndex: number, position: string) => string;
	children?: React.ReactNode;
}

export interface MatchBoxProps {
	match: Nullable<MatchNode>;
	matchPosition: string;
	matchTree: MatchTree;
	teamGap?: number;
	teamHeight?: number;
	setMatchTree?: (matchTree: MatchTree) => void;
	TeamSlotComponent?: React.FC<TeamSlotProps>;
	onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void;
	MatchBoxChildComponent?: React.FC<MatchBoxChildProps>;
}

export interface MatchColumnProps {
	matches: Nullable<MatchNode>[];
	matchPosition: string;
	teamGap?: number;
	teamHeight?: number;
	matchGap?: number;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	MatchBoxComponent?: React.FC<MatchBoxProps>;
	TeamSlotComponent?: React.FC<TeamSlotProps>;
	onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void;
}

export interface BracketProps {
	getHeight?: (numRounds: number) => number;
	getWidth?: (numRounds: number) => number;
	getMatchBoxHeight?: (depth: number) => number;
	getTeamGap?: (depth: number) => number;
	getTeamHeight?: (depth: number) => number;
	matchTree: MatchTree;
	setMatchTree?: (matchTree: MatchTree) => void;
	MatchColumnComponent?: React.FC<MatchColumnProps>;
	MatchBoxComponent?: React.FC<MatchBoxProps>;
	TeamSlotComponent?: React.FC<TeamSlotProps>;
	onTeamClick?: (match: MatchNode, team?: Nullable<Team>) => void;
	lineStyle?: object;
}

export interface MatchBoxChildProps {
	match: MatchNode
	matchTree: MatchTree
	matchPosition: string
	// TeamSlotComponent: React.FC<TeamSlotProps>
	// sloganText?: string
	// bracketLogoBottom?: number[]
	// winnerContainerBottom?: number[]
}


