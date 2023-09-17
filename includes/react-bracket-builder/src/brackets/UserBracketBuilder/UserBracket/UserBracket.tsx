import React, { useState, useEffect, useContext } from 'react';
import * as Sentry from '@sentry/react';
import { bracketApi } from '../../shared/api/bracketApi';
import { useWindowDimensions } from '../../../utils/hooks';
import Spinner from 'react-bootstrap/Spinner'
import { useAppSelector, useAppDispatch } from '../../shared/app/hooks'
import { setMatchTree, selectMatchTree } from '../../shared/features/matchTreeSlice'
import { setNumPages } from '../../shared/features/bracketNavSlice'

import { MatchTree } from '../../shared/models/MatchTree';
import { bracketConstants } from '../../shared/constants';
import { BracketMetaContext, DarkModeContext } from '../../shared/context';
import './UserBracket.scss'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { PickableBracket } from '../../shared/components/Bracket/PickableBracket';


interface ThemeSelectorProps {
	darkMode: boolean;
	setDarkMode: (darkMode: boolean) => void;
}

const ThemeSelector = (props: ThemeSelectorProps) => {
	const {
		darkMode,
		setDarkMode,
	} = props;
	return (
		<div className='tw-flex tw-items-center tw-font-600 tw-gap-14'>
			<span className='tw-text-dd-blue dark:tw-text-white'>Theme</span>
			<button onClick={() => setDarkMode(!darkMode)} className='tw-flex tw-items-center tw-justify-end dark:tw-justify-start tw-w-[71px] tw-h-30 tw-px-2 tw-rounded-16 dark:tw-border-2 tw-border-solid tw-border-white tw-cursor-pointer tw-bg-dd-blue dark:tw-bg-none'>
				<div className='tw-w-[47px] tw-h-[22px] tw-rounded-16 tw-bg-white tw-text-10 tw-flex tw-items-center tw-justify-center'>
					<span className='tw-text-dd-blue tw-font-600 tw-text-sans tw-uppercase'>{darkMode ? 'dark' : 'light'}</span>
				</div>
			</button>
		</div>
	)
}


interface BuyApparelBtnProps {
	onClick?: () => void;
	disabled?: boolean;
	loading?: boolean;
}

const ApparelButton = (props: BuyApparelBtnProps) => {
	const {
		disabled,
		onClick = () => { },
		loading,
	} = props;
	const baseStyles = [
		'tw-border-4',
		'tw-rounded-8',
		'tw-font-700',
		'tw-text-36',
		'tw-px-30',
		'tw-py-14',
		'tw-font-sans',
	]
	const disabledStyles = [
		'tw-border-solid',
		'tw-bg-transparent',
		'tw-text-black/20',
		'dark:tw-text-white/20',
		'dark:tw-border-white/20',
		'tw-border-black/20',
	]
	const enabledStyles = [
		'tw-border-none',
		'tw-bg-green',
		'tw-text-dd-blue',
		'dark:tw-text-white',
		'dark:tw-bg-dark-green',
		'dark:tw-border-green',
		'dark:tw-border-solid'
	]

	const extra = disabled ? disabledStyles : enabledStyles

	const styles = [...baseStyles, ...extra].join(' ')
	return (
		<button className={styles} onClick={onClick} disabled={disabled}>
			{props.loading ?
				//@ts-ignore
				<Spinner variant='light' animation="border" role="status" style={{ borderWidth: '4px' }} />
				: 'ADD TO APPAREL'}
		</button>
	)
}

// const UserBracketBracket = (props: any) => {}

interface UserBracketProps {
	apparelUrl: string;
	bracketStylesheetUrl: string;
	tournament?: any;
	template?: any;
}

interface RenderBracketProps {
	matchTree: MatchTree | null;
	setMatchTree: (matchTree: MatchTree) => void;
	canPick: boolean;
	bracketTitle?: string;
}


const UserBracket = (props: UserBracketProps) => {
	const {
		tournament,
		template,
		apparelUrl,
		bracketStylesheetUrl,
	} = props;

	// const [matchTree, setMatchTree] = useState<Nullable<MatchTree>>(null);
	const [processingImage, setProcessingImage] = useState(false);
	const [darkMode, setDarkMode] = useState(true);
	const [bracketTitle, setBracketTitle] = useState('');
	const [bracketDate, setBracketDate] = useState('');
	const [showPaginated, setShowPaginated] = useState(false);
	// const { width: windowWidth, height: windowHeight } = useWindowDimensions(); // custom hook to get window dimensions
	// const rounds = useAppSelector((state) => state.matchTree.rounds);
	const matchTree = useAppSelector(selectMatchTree);
	const dispatch = useAppDispatch();

	useEffect(() => {
		if (tournament && tournament.bracketTemplate) {
			const template = tournament.bracketTemplate;
			const numTeams = template.numTeams;
			const matches = template.matches;
			setBracketTitle(tournament.title)
			const tree = MatchTree.fromMatchRes(numTeams, matches);
			if (tree) {
				dispatch(setMatchTree(tree.serialize()))
			}
			// Better to have separate components for template and tournament
		} else if (template) {
			console.log('rendering template')
			console.log(template)
			console.log(JSON.stringify(template))
			const numTeams = template.numTeams;
			const matches = template.matches;
			const tree = MatchTree.fromMatchRes(numTeams, matches);
			setBracketTitle(template.title)
			console.log('tree', tree)
			if (tree) {
				dispatch(setMatchTree(tree.serialize()))
			}
		}
	}, []);

	useEffect(() => {
		if (matchTree) {
			// For a paginated bracket there are two pages for all but the last round, plus a landing page and a final page
			const numPages = matchTree.rounds.length * 2 + 1;
			dispatch(setNumPages(numPages))
		}
	}, [matchTree])

	// useEffect(() => {
	// 	if (windowWidth < bracketConstants.paginatedBracketWidth) {
	// 		if (!showPaginated) {
	// 			setShowPaginated(true)
	// 		}
	// 	} else if (showPaginated) {
	// 		setShowPaginated(false)
	// 	}
	// }, [windowWidth])

	const buildPrintHTML = (innerHTML: string, styleUrl: string, inchHeight: number, inchWidth: number,) => {
		const printArea = buildPrintArea(innerHTML, inchHeight, inchWidth)
		// const stylesheet = 'https://backmybracket.com/wp-content/plugins/wp-bracket-builder/includes/react-bracket-builder/build/index.css'
		const stylesheet = 'https://wpbb-stylesheets.s3.amazonaws.com/index.css'
		return `
			<html>
				<head>
					<link rel='stylesheet' href='${stylesheet}' />
				</head>
			<body style='margin: 0; padding: 0;'>
				${printArea}
			</body>
			</html>
		`
	}

	const buildPrintArea = (innerHTML: string, inchHeight: number, inchWidth: number) => {
		const width = inchWidth * 96;
		const height = inchHeight * 96;
		return `
			<div class='wpbb-bracket-print-area' style='height: ${height}px; width: ${width}px; background-color: transparent'>
				${innerHTML}
			</div>
		`
	}

	const getHTML = (): string => {
		const bracketEl = document.getElementsByClassName('wpbb-bracket')[0]
		const bracketHTML = bracketEl.outerHTML
		const bracketCss = bracketStylesheetUrl
		const html = buildPrintHTML(bracketHTML, bracketCss, 16, 12)
		return html
	}

	const minify = (html: string) => {
		return html.replace(/[\n\t]/g, '').replace(/"/g, "'")
	}

	const handleApparelClick = () => {
		// const id = bracketId || bracketRes?.id;
		// if (!id || !matchTree) {
		// 	console.error('no bracket id or match tree')
		// 	return;
		// }
		const html = getHTML()

		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');
		const bracketEl = doc.getElementsByClassName('wpbb-bracket')[0]
		const printArea = doc.getElementsByClassName('wpbb-bracket-print-area')[0]

		// if we were in dark mode, remove it to get the light mode version
		bracketEl.classList.remove('wpbb-dark-mode')
		const lightModeTopHTML = minify(doc.documentElement.outerHTML)
		printArea.classList.add('wpbb-print-center')
		const lightModeCenterHTML = minify(doc.documentElement.outerHTML)
		bracketEl.classList.add('wpbb-dark-mode')
		const darkModeCenterHTML = minify(doc.documentElement.outerHTML)
		printArea.classList.remove('wpbb-print-center')
		const darkModeTopHTML = minify(doc.documentElement.outerHTML)
		// 

		// console.log('light mode top', lightModeTopHTML)
		// console.log('light mode center', lightModeCenterHTML)
		// console.log('dark mode top', darkModeTopHTML)
		// console.log('dark mode center', darkModeCenterHTML)

		// Random key to link the two images together
		const key = Math.random().toString(36).substring(7);
		const promises = [
			bracketApi.htmlToImage({ html: darkModeTopHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `dark`, bracketPlacement: 'top', s3Key: `bracket-${key}-dark-top.png` }),
			bracketApi.htmlToImage({ html: lightModeTopHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `light`, bracketPlacement: 'top', s3Key: `bracket-${key}-light-top.png` }),
			bracketApi.htmlToImage({ html: darkModeCenterHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `dark`, bracketPlacement: 'center', s3Key: `bracket-${key}-dark-center.png` }),
			bracketApi.htmlToImage({ html: lightModeCenterHTML, inchHeight: 16, inchWidth: 12, deviceScaleFactor: 1, themeMode: `light`, bracketPlacement: 'center', s3Key: `bracket-${key}-light-center.png` }),
		]
		setProcessingImage(true)
		Promise.all(promises).then((res) => {
			// console.log('res')
			// console.log(res)
			// setProcessingImage(false)
			window.location.href = apparelUrl
		}).catch((err) => {
			setProcessingImage(false)
			console.error(err)
			Sentry.captureException(err)
		})
	}

	// const getTeamSlotComponent = () => {
	// 	return (
	// 		<DefaultTeamSlot />
	// 	)
	// }

	// const getMatchBoxComponent = () => {
	// 	return (
	// 		<DefaultMatchBox
	// 			TeamSlotComponent={getTeamSlotComponent()}
	// 		/>

	// 	)
	// }

	// const getMatchColumnComponent = () => {
	// 	return ()
	// }

	const renderPlayTournamentBracket = (bracketProps: RenderBracketProps) => {
		const { matchTree } = bracketProps
		if (!matchTree) {
			return <></>
		}
		// const disableActions = matchTree === null || !matchTree.allPicked() || processingImage
		const disableActions = false;
		// const disableActions = processingImage
		const numRounds = matchTree?.rounds.length;
		// const pickedWinner = matchTree?.allPicked();
		console.log('numRounds', numRounds)
		const actionButtonMargin = bracketConstants.bracketActionsMarginTop[numRounds]

		return (
			<div className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}>
				<div className='tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center'>
					<ThemeSelector darkMode={darkMode} setDarkMode={setDarkMode} />
				</div>
				{/* <div className='tw-flex tw-flex-col tw-justify-center tw-items-center tw-min-h-[500px] tw-m-auto'> */}
				<PickableBracket
					matchTree={matchTree}
					setMatchTree={(matchTree: MatchTree) => dispatch(setMatchTree(matchTree.serialize()))}
				/>
				{/* </div> */}
				<div className='tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center'>
					<ApparelButton disabled={disableActions} loading={processingImage} onClick={handleApparelClick} />
				</div>
			</div>
		)
	}

	const renderPaginatedBracket = (bracketProps: RenderBracketProps) => {
		// return (
		// 	// <PaginatedUserBracket {...bracketProps} />
		// )
	}

	const bracketProps = {
		matchTree,
		setMatchTree: (matchTree: MatchTree) => dispatch(setMatchTree(matchTree.serialize())),
		canPick: true,
		bracketTitle: tournament?.title
	}

	return (
		<BracketMetaContext.Provider value={{ title: bracketTitle, date: bracketDate }}>
			<DarkModeContext.Provider value={darkMode}>
				{/* <div className='tw-h-[800px] tw-bg-[url("http://localhost:8888/wordpress-new/wp-content/uploads/2023/09/bracket-bg-dark.png")]'> */}
				<div className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${darkMode ? ' tw-dark' : ''}`} style={{ 'backgroundImage': `url(${darkMode ? darkBracketBg : lightBracketBg})` }}>
					{renderPlayTournamentBracket(bracketProps)}
					{/* {showPaginated ? renderPaginatedBracket(bracketProps) : renderPairedBracket(bracketProps)} */}

				</div>
			</DarkModeContext.Provider>
		</BracketMetaContext.Provider>
	)
}

export default UserBracket