import React from 'react';
import { ReactComponent as UserIcon } from '../assets/user.svg'


export const ProfilePicture = ({ src, alt, color, backgroundColor, shadow }: { src: string; alt: string; color?: string; backgroundColor?: string, shadow?: boolean }) => {
    let class_name = `tw-h-50 tw-w-50 tw-flex tw-rounded-full tw-border-solid tw-border-1 tw-border-${color} tw-justify-center tw-items-center tw-bg-${backgroundColor}`;
    class_name = shadow ? class_name + ` tw-shadow-lg tw-shadow-${color}` : class_name
    const svg_color = `tw-text-${color}`
    return (
        <div>
            {src ? 
                <img
                    className={shadow? `tw-h-50 tw-w-50 tw-rounded-full tw-shadow-lg tw-shadow-${color}` :"tw-h-50 tw-w-50 tw-rounded-full"}
                    src={src}
                    alt={alt}
                />
            :
                <div className={class_name}>
                    <UserIcon className={svg_color} />
                </div>
            }
        </div>
    );
};
