import { Nullable } from '../../../../utils/types';


export interface TeamRes {
	id: number;
	name: string;
}

interface BracketBase {
	name: string;
	active: boolean;
	numRounds: number;
	numWildcards: number;
	wildcardPlacement: number;
}

interface phpDate {
	date: string;
	timezone_type: number;
	timezone: string;
}

// export interface BracketRes extends BracketBase {
// 	id: number;
// 	createdAt: phpDate;
// 	numSubmissions: number;
// 	rounds: RoundRes[];
// }

// export interface BracketReq extends BracketBase {
// 	rounds: RoundReq[];
// }

// export interface SubmissionRes {
// 	id: number;
// 	// createdAt: phpDate;
// 	bracketId: number;
// 	customerId: number;
// 	name: string;
// 	rounds: RoundRes[];
// }

// export interface SubmissionReq {
// 	bracketId: number;
// 	name: string;
// 	html: string;
// 	rounds?: SubmissionRoundReq[];
// }

// export interface SubmissionRoundReq {
// 	matches: Nullable<SubmissionMatchReq>[];
// }

// export interface SubmissionMatchReq {
// 	result: Nullable<SubmissionTeamReq>;
// }

// export interface SubmissionTeamReq {
// 	id: number;
// }

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

export interface MatchRes {
	id: number;
	roundIndex: number;
	matchIndex: number;
	team1?: TeamRes;
	team2?: TeamRes;
}

export interface MatchPicksRes {
	roundIndex: number;
	matchIndex: number;
	winningTeamId: number;
}

export interface TeamRepr {
	id?: number;
	name: string;
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


