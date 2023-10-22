import React, { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context'
import { BustPlayBuilder } from './BustPlayBuilder'
// import { ReactComponent as UserIcon } from '../../shared/assets/user.svg'
import { ProfilePicture } from '../../shared/components/ProfilePicture'
import { PlayRes } from '../../shared/api/types/bracket'
import { ReactComponent as LightningIcon } from '../../shared/assets/lightning.svg'

interface BustPlayPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  bracketPlay: PlayRes
  redirectUrl: string
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
  thumbnailUrl: string
}

const BustPlayPage = (props: BustPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    matchTree,
    setMatchTree,
    bracketPlay: play,
    redirectUrl,
    thumbnailUrl,
  } = props

  console.log('bust play', play)

  const [page, setPage] = useState('view')

  const actionButtonCallback = async () => {
    setPage('bust')
  }

  useEffect(() => {
    const picks = play?.picks
    const bracketTitle = play?.bracket?.title
    const authorDisplayName = play?.authorDisplayName
    const title = authorDisplayName
      ? `${authorDisplayName}'s ${bracketTitle} picks`
      : bracketTitle
    setBracketMeta({
      title,
      month: play?.bracket?.month,
      year: play?.bracket?.year,
    })
    const bracket = play?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    if (picks && matches) {
      const tree = MatchTree.fromPicks(numTeams, matches, picks)

      if (tree) {
        setMatchTree(tree)
      }
    }
  }, [play])

  if (page === 'bust' && matchTree) {
    return (
      <BustPlayBuilder
        matchTree={matchTree}
        setMatchTree={setMatchTree}
        redirectUrl={redirectUrl}
        busteePlay={play}
        thumbnailUrl={thumbnailUrl}
      />
    )
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
      >
        {matchTree && (
          <>
            <div className="tw-mb-40 tw-mt-40 tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ProfilePicture
                src={thumbnailUrl}
                alt="celebrity-photo"
                color="blue"
                shadow={false}
              />
            </div>
            <PickableBracket matchTree={matchTree} />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="big-red"
                darkMode={darkMode}
                onClick={actionButtonCallback}
              >
                <LightningIcon />
                Bust Bracket
              </ActionButton>
            </div>
          </>
        )}
      </div>
    </div>
  )
}

const WrappedBustPlayPage = WithProvider(
  WithMatchTree(WithBracketMeta(WithDarkMode(BustPlayPage)))
)
export default WrappedBustPlayPage
