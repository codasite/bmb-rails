import { useEffect, useState } from 'react'
import * as Sentry from '@sentry/react'
import { MatchTree } from '../../shared/models/MatchTree'
import { BusterBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import redBracketBg from '../../shared/assets/bracket-bg-red.png'
import { bracketApi } from '../../shared/api/bracketApi'
import { PlayReq, PlayRes } from '../../shared/api/types/bracket'
import { BusterVsBustee } from './BusterVersusBustee'
import { useWindowDimensions } from '../../../utils/hooks'
import { getBracketWidth } from '../../shared/components/Bracket/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { PaginatedBuilder } from '../PaginatedBuilderBase/PaginatedBuilder'
import { getBustTrees } from './utils'
import { BustEndPage, BustBracketPages } from './PaginatedBustBuilder'
import { BracketBusterContext } from './context'

interface BustPlayBuilderProps {
  busteePlay: PlayRes
  redirectUrl: string
  bracket?: any
}

export const BustPlayBuilder = (props: BustPlayBuilderProps) => {
  const { busteePlay, redirectUrl, bracket } = props

  const [busteeThumbnail, setBusteeThumbnail] = useState<string>('')
  const [busteeDisplayName, setBusteeDisplayName] = useState<string>('')
  const [processing, setProcessing] = useState<boolean>(false)

  const { busterTree, setBusterTree } = getBustTrees()

  useEffect(() => {
    setVersus()
    buildMatchTrees()
  }, [])

  const setVersus = () => {
    const busteeName = busteePlay?.authorDisplayName
    const busteeThumbnail = busteePlay?.thumbnailUrl
    setBusteeDisplayName(busteeName)
    setBusteeThumbnail(busteeThumbnail)
  }

  const buildMatchTrees = () => {
    console.log('buildMatchTrees')
    const bracket = busteePlay?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    const buster = MatchTree.fromMatchRes(numTeams, matches)
    console.log('buster', buster)
    setBusterTree(buster)
  }

  const handleSubmit = () => {
    const picks = busterTree?.toMatchPicks()
    const bracketId = busteePlay?.bracket?.id
    const busteeId = busteePlay?.id

    if (!bracketId || !busteeId || !picks) {
      const msg =
        'Cannot create play. Missing one of bracketId, busteeId, or picks'
      console.error(msg)
      Sentry.captureException(msg)
      return
    }

    bracketApi.createPlay

    const playReq: PlayReq = {
      bracketId: bracketId,
      picks: picks,
      bustedId: busteeId,
    }

    setProcessing(true)
    bracketApi
      .createPlay(playReq)
      .then(async (res) => {
        // time out to allow for play to be created
        await new Promise((r) => setTimeout(r, 1000))
        window.location.href = redirectUrl
      })
      .catch((err) => {
        console.error(err)
        Sentry.captureException(err)
      })
      .finally(() => {
        setProcessing(false)
      })

    window.location.href = redirectUrl
  }
  console.log('buster picks', busterTree?.toMatchPicks())

  const { width: windowWidth, height: windowHeight } = useWindowDimensions()

  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  if (showPaginated && busterTree) {
    return (
      <BracketBusterContext.Provider
        value={{
          busteeDisplayName,
          busteeThumbnail,
        }}
      >
        <PaginatedBuilder
          EndPageComponent={BustEndPage}
          BracketPagesComponent={BustBracketPages}
          handleSubmit={handleSubmit}
          matchTree={busterTree}
          setMatchTree={setBusterTree}
        />
      </BracketBusterContext.Provider>
    )
  }

  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover$`}
      style={{
        backgroundImage: `url(${redBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto`}
      >
        {busterTree && (
          <>
            <BusterVsBustee
              busteeDisplayName={busteeDisplayName}
              busteeThumbnail={busteeThumbnail}
            />
            <BusterBracket
              matchTree={busterTree}
              setMatchTree={setBusterTree}
            />
            <div className="tw-h-[260px] tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ActionButton
                variant="red"
                size="big"
                darkMode={true}
                disabled={!busterTree?.allPicked() || processing}
                onClick={handleSubmit}
              >
                Submit
              </ActionButton>
            </div>
          </>
        )}
      </div>
    </div>
  )
}
