import { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { bracketApi } from '../../shared/api/bracketApi'
import { PlayReq, PlayRes } from '../../shared/api/types/bracket'
import { getBustTrees } from './utils'
import { BustPlayView } from './BustPlayView'
import { logger } from '../../../utils/Logger'

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
    const bracket = busteePlay?.bracket
    const buster = MatchTree.fromMatchRes(bracket)
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
      logger.error(msg)
      return
    }

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
        logger.error(err)
      })
      .finally(() => {
        setProcessing(false)
      })

    window.location.href = redirectUrl
  }
  return (
    <BustPlayView
      bracket={bracket}
      busterTree={busterTree}
      setBusterTree={setBusterTree}
      busteeDisplayName={busteeDisplayName}
      busteeThumbnail={busteeThumbnail}
      onButtonClick={handleSubmit}
      buttonText="Submit"
      processing={processing}
    />
  )
}
