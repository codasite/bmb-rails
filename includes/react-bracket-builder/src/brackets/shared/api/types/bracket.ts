import { Nullable } from '../../../../utils/types';
import { WildcardPlacement } from '../../models/MatchTree';

interface phpDate {
	date: string;
	timezone_type: number;
	timezone: string;
}

export interface HTMLtoImageReq {
	html: string;
	inchHeight: number;
	inchWidth: number;
	deviceScaleFactor?: number;
	themeMode?: string;
	bracketPlacement?: string;
	key?: string;
	s3Key?: string;
}

export interface HTMLtoImageRes {
	imageUrl: string;
}

export interface TeamRes {
	id: number;
	name: string;
}

export interface TeamReq {
	name: string;
}

export interface MatchRes {
	id: number;
	roundIndex: number;
	matchIndex: number;
	team1?: Nullable<TeamRes>;
	team2?: Nullable<TeamRes>;
}

export interface MatchReq {
	roundIndex: number;
	matchIndex: number;
	team1?: TeamReq;
	team2?: TeamReq;
}

export interface MatchPicks {
	roundIndex: number;
	matchIndex: number;
	winningTeamId: number;
}

export interface TeamRepr {
	id?: number;
	name: string;
}

export interface MatchTreeRepr {
	rounds: Nullable<MatchRepr>[][];
	wildcardPlacement?: WildcardPlacement;
}

export interface MatchRepr {
	id?: number;
	roundIndex: number;
	matchIndex: number;
	team1?: TeamRepr;
	team2?: TeamRepr;
	team1Wins?: boolean;
	team2Wins?: boolean;
}

export interface TemplateReq {
	title: string;
	numTeams: number;
	status?: string;
	wildcardPlacement: WildcardPlacement;
	matches: MatchReq[];
}

export interface TemplateRes {
	id: number;
	title: string;
	numTeams: number;
	status: string;
	date: phpDate;
	dateGmt: phpDate;
	wildcardPlacement: WildcardPlacement;
	html: string;
	imgUrl: string;
	matches?: MatchRes[];
}


